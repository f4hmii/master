<?php
session_start();
include 'db_connection.php';
require_once 'vendor/autoload.php';

// Set konfigurasi Midtrans
\Midtrans\Config::$serverKey = 'SB-Mid-server-nA6_tkM6Ej16vJPJ_lrhwGte'; // Ganti dengan Server Key Anda
\Midtrans\Config::$isProduction = false; // Ganti ke true jika sudah live
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

if (!isset($_SESSION['id'])) {
    die("Silakan login terlebih dahulu.");
}

$pengguna_id = intval($_SESSION['id']);

// Cek apakah ini permintaan untuk membuat pesanan baru (dari keranjang)
// Atau permintaan untuk memicu pembayaran (dari detail_checkout.php)
$is_new_order_request = ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['pesanan_id_to_pay']));
$is_pay_request = (isset($_GET['action']) && $_GET['action'] === 'pay' && isset($_GET['pesanan_id']));

if ($is_new_order_request) {
    // --- PROSES MEMBUAT PESANAN BARU DARI KERANJANG ---

    // Ambil data pengguna dari database
    $stmt_user = $conn->prepare("SELECT nama_pengguna, email, nomor_telepon, alamat FROM pengguna WHERE pengguna_id = ?");
    $stmt_user->bind_param("i", $pengguna_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    $user_data = $result_user->fetch_assoc();
    $stmt_user->close();

    if (!$user_data) {
        die("Data pengguna tidak ditemukan.");
    }

    // Ambil item dari keranjang belanja
    $stmt_cart = $conn->prepare("
        SELECT c.produk_id, c.nama_produk, c.harga, c.quantity, c.color, c.size
        FROM cart c
        WHERE c.pengguna_id = ?
    ");
    $stmt_cart->bind_param("i", $pengguna_id);
    $stmt_cart->execute();
    $cart_items_result = $stmt_cart->get_result();
    $cart_items = $cart_items_result->fetch_all(MYSQLI_ASSOC);
    $stmt_cart->close();

    if (count($cart_items) === 0) {
        die("Keranjang belanja kosong. Tidak dapat membuat pesanan.");
    }

    $total_harga_pesanan = 0;
    foreach ($cart_items as $item) {
        $total_harga_pesanan += $item['harga'] * $item['quantity'];
    }
    $shipping_cost = 20000; // Biaya pengiriman
    $total_harga_pesanan += $shipping_cost;

    // Buat pesanan baru di tabel 'pesanan'
    $stmt_insert_pesanan = $conn->prepare("INSERT INTO pesanan (buyer_id, tanggal_pesan, status, total_harga) VALUES (?, NOW(), ?, ?)");
    $status_pesanan = 'tertunda_pembayaran';
    $stmt_insert_pesanan->bind_param("isd", $pengguna_id, $status_pesanan, $total_harga_pesanan);
    $stmt_insert_pesanan->execute();
    $pesanan_id_baru = $conn->insert_id; // Ambil ID pesanan yang baru dibuat
    $stmt_insert_pesanan->close();

    // Simpan detail pesanan ke tabel 'pesanandetail'
    $stmt_insert_detail_pesanan = $conn->prepare("INSERT INTO pesanandetail (pesanan_id, produk_id, quantity, harga_produk, color, size) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($cart_items as $item) {
        $stmt_insert_detail_pesanan->bind_param("iiidss", $pesanan_id_baru, $item['produk_id'], $item['quantity'], $item['harga'], $item['color'], $item['size']);
        $stmt_insert_detail_pesanan->execute();
    }
    $stmt_insert_detail_pesanan->close();

    // Kosongkan keranjang belanja
    $stmt_clear_cart = $conn->prepare("DELETE FROM cart WHERE pengguna_id = ?");
    $stmt_clear_cart->bind_param("i", $pengguna_id);
    $stmt_clear_cart->execute();
    $stmt_clear_cart->close();

    // Arahkan ke halaman detail_checkout.php dengan pesanan_id yang baru dibuat
    header("Location: detail_checkout.php?pesanan_id=" . $pesanan_id_baru);
    exit();

} elseif ($is_pay_request) {
    // --- PROSES MEMICU PEMBAYARAN MIDTRANS UNTUK PESANAN YANG SUDAH ADA ---

    $pesanan_id = intval($_GET['pesanan_id']);

    // Ambil detail pesanan yang sudah ada
    $stmt_pesanan = $conn->prepare("SELECT * FROM pesanan WHERE pesanan_id = ? AND buyer_id = ?");
    $stmt_pesanan->bind_param("ii", $pesanan_id, $pengguna_id);
    $stmt_pesanan->execute();
    $pesanan = $stmt_pesanan->get_result()->fetch_assoc();
    $stmt_pesanan->close();

    if (!$pesanan) {
        die("Pesanan tidak ditemukan atau Anda tidak memiliki akses untuk membayarnya.");
    }

    // Ambil data pembeli
    $stmt_user = $conn->prepare("SELECT nama_pengguna, email, nomor_telepon, alamat FROM pengguna WHERE pengguna_id = ?");
    $stmt_user->bind_param("i", $pengguna_id);
    $stmt_user->execute();
    $user_data = $stmt_user->get_result()->fetch_assoc();
    $stmt_user->close();

    // Ambil item detail dari pesanan yang sudah ada di tabel 'pesanandetail'
    $stmt_detail_pesanan = $conn->prepare("
        SELECT pd.produk_id, pd.quantity, pd.harga_produk, p.nama_produk
        FROM pesanandetail pd
        JOIN produk p ON pd.produk_id = p.produk_id
        WHERE pd.pesanan_id = ?
    ");
    $stmt_detail_pesanan->bind_param("i", $pesanan_id);
    $stmt_detail_pesanan->execute();
    $items_from_order = $stmt_detail_pesanan->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_detail_pesanan->close();

    if (count($items_from_order) === 0) {
        die("Tidak ada item di pesanan ini untuk diproses pembayaran.");
    }

    $item_details_midtrans = [];
    foreach ($items_from_order as $item) {
        $item_details_midtrans[] = [
            'id' => $item['produk_id'],
            'price' => (int)$item['harga_produk'],
            'quantity' => (int)$item['quantity'],
            'name' => $item['nama_produk'],
        ];
    }

    // Tambahkan biaya pengiriman ke item details Midtrans (sesuai yang dihitung saat buat pesanan)
    $shipping_cost = 20000;
    $item_details_midtrans[] = [
        'id' => 'SHIPPING',
        'price' => $shipping_cost,
        'quantity' => 1,
        'name' => 'Biaya Pengiriman',
    ];

    $params = [
        'transaction_details' => [
            'order_id' => $pesanan['pesanan_id'] . '-' . uniqid(), // Gunakan ID pesanan + uniqid untuk order_id Midtrans
            'gross_amount' => (int)$pesanan['total_harga'],
        ],
        'item_details' => $item_details_midtrans,
        'customer_details' => [
            'first_name' => $user_data['nama_pengguna'],
            'email' => $user_data['email'],
            'phone' => $user_data['nomor_telepon'],
            'billing_address' => [
                'address' => $user_data['alamat'],
                'city' => 'Bandung',
                'postal_code' => '40111',
                'phone' => $user_data['nomor_telepon'],
                'country_code' => 'IDN',
            ],
        ],
    ];

    try {
        $snapToken = \Midtrans\Snap::getSnapToken($params);
    } catch (Exception $e) {
        echo "Error mendapatkan Snap Token: " . $e->getMessage();
        exit;
    }
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Lanjutkan Pembayaran</title>
        <script type="text/javascript"
            src="https://app.sandbox.midtrans.com/snap/snap.js"
            data-client-key="SB-Mid-client-jJ0wxFyBAyYOQ0ky"></script> <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gray-100 flex items-center justify-center min-h-screen">
        <div class="bg-white p-8 rounded-lg shadow-md text-center">
            <h2 class="text-2xl font-bold mb-4">Konfirmasi Pembayaran</h2>
            <p class="text-lg mb-2">Total yang harus dibayar: <span class="font-semibold">Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?></span></p>
            <button id="pay-button" class="mt-4 bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">Bayar Sekarang</button>
            <p class="text-sm text-gray-500 mt-2">Anda akan diarahkan ke halaman pembayaran Midtrans.</p>
        </div>

        <script type="text/javascript">
            const currentPesananId = <?= $pesanan_id ?>;

            document.getElementById('pay-button').onclick = function(){
                snap.pay('<?= $snapToken ?>', {
                    onSuccess: function(result){
                        alert("Pembayaran berhasil!");
                        // Arahkan kembali ke detail_checkout.php setelah sukses
                        window.location.href = 'detail_checkout.php?pesanan_id=' + currentPesananId + '&status=success';
                    },
                    onPending: function(result){
                        alert("Pembayaran tertunda!");
                        // Arahkan kembali ke detail_checkout.php setelah pending
                        window.location.href = 'detail_checkout.php?pesanan_id=' + currentPesananId + '&status=pending';
                    },
                    onError: function(result){
                        alert("Pembayaran gagal!");
                        // Arahkan kembali ke detail_checkout.php setelah gagal
                        window.location.href = 'detail_checkout.php?pesanan_id=' + currentPesananId + '&status=error';
                    },
                    onClose: function(){
                        alert('Anda menutup popup tanpa menyelesaikan pembayaran');
                        // Arahkan kembali ke detail_checkout.php jika popup ditutup
                        window.location.href = 'detail_checkout.php?pesanan_id=' + currentPesananId + '&status=closed';
                    }
                });
            };
        </script>
    </body>
    </html>

<?php
} else {
    // Jika akses langsung ke checkout.php tanpa POST dari cart atau GET action=pay
    die("Akses tidak valid.");
}
?>