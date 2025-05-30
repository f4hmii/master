<?php
session_start();
include 'db_connection.php';
require_once 'vendor/autoload.php'; // Pastikan path ini benar

// Set konfigurasi Midtrans
\Midtrans\Config::$serverKey = 'SB-Mid-server-nA6_tkM6Ej16vJPJ_lrhwGte'; // Ganti dengan Server Key Anda
\Midtrans\Config::$isProduction = false; // Ganti ke true jika sudah live
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

if (!isset($_SESSION['id'])) {
    die("Silakan login terlebih dahulu.");
}

$pengguna_id = intval($_SESSION['id']);

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
    SELECT c.produk_id, c.nama_produk, c.harga, c.quantity, p.foto_url 
    FROM cart c
    JOIN produk p ON c.produk_id = p.produk_id
    WHERE c.pengguna_id = ?
");
$stmt_cart->bind_param("i", $pengguna_id);
$stmt_cart->execute();
$cart_items_result = $stmt_cart->get_result();
$stmt_cart->close();

$transaction_details = [
    'order_id' => 'ORDER-' . uniqid(),
    'gross_amount' => 0,
];

$item_details = [];
$total_harga = 0;

if ($cart_items_result->num_rows > 0) {
    while ($item = $cart_items_result->fetch_assoc()) {
        $sub_total = $item['harga'] * $item['quantity'];
        $total_harga += $sub_total;

        $item_details[] = [
            'id' => $item['produk_id'],
            'price' => (int)$item['harga'],
            'quantity' => (int)$item['quantity'],
            'name' => $item['nama_produk'],
        ];
    }
} else {
    die("Keranjang belanja kosong.");
}

// Tambahkan biaya pengiriman (contoh)
$shipping_cost = 20000; // Contoh biaya pengiriman
$total_harga += $shipping_cost;

$item_details[] = [
    'id' => 'SHIPPING',
    'price' => $shipping_cost,
    'quantity' => 1,
    'name' => 'Biaya Pengiriman',
];

$transaction_details['gross_amount'] = (int)$total_harga;

$customer_details = [
    'first_name' => $user_data['nama_pengguna'],
    'email' => $user_data['email'],
    'phone' => $user_data['nomor_telepon'],
    'billing_address' => [
        'address' => $user_data['alamat'],
        'city' => 'Bandung', // Contoh kota, Anda bisa tambahkan field ini di tabel pengguna
        'postal_code' => '40111', // Contoh kode pos
        'phone' => $user_data['nomor_telepon'],
        'country_code' => 'IDN',
    ],
];

$params = [
    'transaction_details' => $transaction_details,
    'item_details' => $item_details,
    'customer_details' => $customer_details,
];

try {
    $snapToken = \Midtrans\Snap::getSnapToken($params);
} catch (Exception $e) {
    echo $e->getMessage();
    exit;
}

// Simpan detail pesanan ke database sebelum redirect ke Midtrans
// Buat pesanan baru
$stmt_insert_pesanan = $conn->prepare("INSERT INTO pesanan (buyer_id, tanggal_pesan, status, total_harga) VALUES (?, NOW(), ?, ?)");
$status_pesanan = 'tertunda_pembayaran';
$stmt_insert_pesanan->bind_param("isd", $pengguna_id, $status_pesanan, $total_harga);
$stmt_insert_pesanan->execute();
$pesanan_id = $conn->insert_id;
$stmt_insert_pesanan->close();

// Simpan detail pesanan
$stmt_insert_detail_pesanan = $conn->prepare("INSERT INTO pesanan_detail (pesanan_id, produk_id, jumlah, harga_satuan) VALUES (?, ?, ?, ?)");
foreach ($cart_items_result as $item) { // Harus di-reset result set-nya atau query ulang
    $stmt_insert_detail_pesanan->bind_param("iiid", $pesanan_id, $item['produk_id'], $item['quantity'], $item['harga']);
    $stmt_insert_detail_pesanan->execute();
}
$stmt_insert_detail_pesanan->close();

// Kosongkan keranjang belanja setelah berhasil membuat pesanan
$stmt_clear_cart = $conn->prepare("DELETE FROM cart WHERE pengguna_id = ?");
$stmt_clear_cart->bind_param("i", $pengguna_id);
$stmt_clear_cart->execute();
$stmt_clear_cart->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <script type="text/javascript"
        src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="SB-Mid-client-jJ0wxFyBAyYOQ0ky"></script> <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md text-center">
        <h2 class="text-2xl font-bold mb-4">Ringkasan Pesanan</h2>
        <p class="text-lg mb-2">Total yang harus dibayar: <span class="font-semibold">Rp <?= number_format($total_harga, 0, ',', '.') ?></span></p>
        <button id="pay-button" class="mt-4 bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">Bayar Sekarang</button>
        <p class="text-sm text-gray-500 mt-2">Anda akan diarahkan ke halaman pembayaran Midtrans.</p>
    </div>

    <script type="text/javascript">
        document.getElementById('pay-button').onclick = function(){
            // SnapToken sudah didapatkan dari PHP
            snap.pay('<?= $snapToken ?>', {
                onSuccess: function(result){
                    /* You may add your own implementation here */
                    alert("Pembayaran berhasil!");
                    window.location.href = '../index.php'; // Redirect ke halaman utama atau halaman sukses
                },
                onPending: function(result){
                    /* You may add your own implementation here */
                    alert("Pembayaran tertunda!");
                    window.location.href = '../index.php'; // Redirect ke halaman utama
                },
                onError: function(result){
                    /* You may add your own implementation here */
                    alert("Pembayaran gagal!");
                    window.location.href = '../index.php'; // Redirect ke halaman utama
                },
                onClose: function(){
                    /* You may add your own implementation here */
                    alert('Anda menutup popup tanpa menyelesaikan pembayaran');
                    window.location.href = '../index.php'; // Redirect ke halaman utama
                }
            });
        };
    </script>
</body>
</html>