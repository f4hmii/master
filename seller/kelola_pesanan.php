<?php
session_start(); // Memulai sesi PHP
include '../db_connection.php'; // Menyertakan file koneksi database
include '../view/header.php'; // Menyertakan header

// Pastikan user login dan role seller
if (!isset($_SESSION['id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') { // Memeriksa status login dan role pengguna
    header('Location: ../pages/login.php'); // Mengarahkan ke halaman login jika tidak valid
    exit; // Menghentikan eksekusi skrip
}
$seller_id = intval($_SESSION['id']); // Mengambil ID seller dari sesi

// Ambil semua pesanan yang relevan untuk penjual ini
// Status: dibayar, diproses_penjual, dikirim
$query_pesanan = $conn->prepare("
    SELECT
        ps.pesanan_id,
        ps.status,
        ps.tanggal_pesan,
        SUM(pd.quantity * pd.harga_produk) AS total_subharga_per_pesanan,
        GROUP_CONCAT(
            CONCAT_WS(':', p.nama_produk, pd.quantity, pd.harga_produk, pd.color, pd.size, p.foto_url)
            ORDER BY p.nama_produk ASC SEPARATOR ';'
        ) AS detail_produk_json
    FROM pesanandetail pd
    JOIN produk p ON pd.produk_id = p.produk_id
    JOIN pesanan ps ON pd.pesanan_id = ps.pesanan_id
    WHERE p.seller_id = ? AND ps.status IN ('dibayar', 'diproses_penjual', 'dikirim')
    GROUP BY ps.pesanan_id, ps.status, ps.tanggal_pesan
    ORDER BY ps.tanggal_pesan DESC
");
$query_pesanan->bind_param("i", $seller_id); // Mengikat parameter seller_id
$query_pesanan->execute(); // Mengeksekusi query
$result_pesanan = $query_pesanan->get_result(); // Mengambil hasil query
$query_pesanan->close(); // Menutup statement

$orders = []; // Inisialisasi array untuk menyimpan pesanan
while ($row = $result_pesanan->fetch_assoc()) { // Melakukan iterasi pada setiap baris hasil query
    $order = $row; // Menyimpan baris saat ini sebagai order
    $order['produk_details'] = []; // Inisialisasi array untuk detail produk

    // Pisahkan detail produk yang digabungkan
    $product_strings = explode(';', $row['detail_produk_json']); // Memisahkan string detail produk
    foreach ($product_strings as $prod_str) { // Melakukan iterasi pada setiap string produk
        $parts = explode(':', $prod_str, 6); // Memisahkan bagian-bagian produk
        if (count($parts) === 6) { // Memeriksa apakah semua bagian produk lengkap
            $order['produk_details'][] = [ // Menambahkan detail produk ke order
                'nama_produk' => $parts[0], // Nama produk
                'quantity' => intval($parts[1]), // Kuantitas produk
                'harga_produk' => floatval($parts[2]), // Harga produk
                'color' => $parts[3], // Warna produk
                'size' => $parts[4], // Ukuran produk
                'foto_url' => $parts[5] // URL foto produk
            ];
        }
    }
    unset($order['detail_produk_json']); // Menghapus kolom detail_produk_json yang tidak lagi diperlukan

    $orders[] = $order; // Menambahkan order ke array orders
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Pesanan - Penjual</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <style>
        .order-card {
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .order-card:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <div class="max-w-4xl mx-auto p-6 bg-white shadow-md rounded-lg mt-8">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Daftar Pesanan Saya</h1>

        <div class="mb-4">
            <a href="profile.php" class="inline-block bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition-colors">
                ← Kembali ke Profil
            </a>
        </div>

        <?php if (empty($orders)): ?>
            <p class="text-gray-600 text-center">Belum ada pesanan yang perlu diproses.</p>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="flex justify-between items-center mb-3">
                        <h2 class="text-lg font-semibold text-gray-700">Pesanan ID: <?= htmlspecialchars($order['pesanan_id']) ?></h2>
                        <span class="px-3 py-1 rounded-full text-xs font-medium
                            <?php
                                if ($order['status'] === 'dibayar') echo 'bg-green-100 text-green-800';
                                elseif ($order['status'] === 'diproses_penjual') echo 'bg-yellow-100 text-yellow-800';
                                elseif ($order['status'] === 'dikirim') echo 'bg-blue-100 text-blue-800';
                                else echo 'bg-gray-100 text-gray-800';
                            ?>">
                            <?= htmlspecialchars(str_replace('_', ' ', $order['status'])) ?>
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 mb-4">Tanggal Pesan: <?= htmlspecialchars($order['tanggal_pesan']) ?></p>

                    <div class="space-y-3">
                        <?php foreach ($order['produk_details'] as $product): ?>
                            <div class="flex items-start bg-gray-50 p-3 rounded-md">
                                <img src="../uploads/<?= htmlspecialchars($product['foto_url']) ?>" alt="<?= htmlspecialchars($product['nama_produk']) ?>"
                                    class="w-20 h-20 object-cover rounded-md mr-4"
                                    onerror="this.onerror=null; this.src='../uploads/image-not-found.png';" />
                                <div>
                                    <h3 class="font-medium text-gray-800"><?= htmlspecialchars($product['nama_produk']) ?></h3>
                                    <p class="text-sm text-gray-600">Kuantitas: <?= htmlspecialchars($product['quantity']) ?></p>
                                    <p class="text-sm text-gray-600">Harga Satuan: Rp <?= number_format($product['harga_produk'], 0, ',', '.') ?></p>
                                    <p class="text-sm text-gray-600">Warna: <?= htmlspecialchars($product['color']) ?>, Ukuran: <?= htmlspecialchars($product['size']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-4 flex justify-between items-center">
                        <span class="text-md font-bold text-gray-700">Total: Rp <?= number_format($order['total_subharga_per_pesanan'], 0, ',', '.') ?></span>
                        <div>
                            <?php if ($order['status'] === 'dibayar' || $order['status'] === 'diproses_penjual'): ?>
                                <button onclick="markAsShipped(<?= $order['pesanan_id'] ?>)"
                                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                                    Tandai Dikirim
                                </button>
                            <?php elseif ($order['status'] === 'dikirim'): ?>
                                <button onclick="markAsCompleted(<?= $order['pesanan_id'] ?>)"
                                    class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors">
                                    Tandai Selesai
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        function markAsShipped(pesananId) { // Mendefinisikan fungsi markAsShipped
            if (confirm('Anda yakin ingin menandai pesanan ' + pesananId + ' sebagai DIKIRIM?')) { // Konfirmasi pengiriman pesanan
                fetch('update_pesanan_status_seller.php', { // Memanggil skrip update_pesanan_status_seller.php
                    method: 'POST', // Menggunakan metode POST
                    headers: {
                        'Content-Type': 'application/json', // Mengatur header Content-Type
                    },
                    body: JSON.stringify({
                        pesanan_id: pesananId, // ID pesanan
                        status: 'dikirim' // Mengatur status pesanan menjadi 'dikirim'
                    })
                })
                .then(response => response.json()) // Menguraikan respons JSON
                .then(data => {
                    if (data.success) { // Memeriksa apakah pembaruan status berhasil
                        alert('Pesanan ' + pesananId + ' berhasil ditandai sebagai dikirim.'); // Menampilkan pesan sukses
                        location.reload(); // Memuat ulang halaman
                    } else {
                        alert('Gagal menandai pesanan sebagai dikirim: ' + data.message); // Menampilkan pesan error
                    }
                })
                .catch(error => {
                    console.error('Error:', error); // Log error
                    alert('Terjadi kesalahan jaringan.'); // Menampilkan pesan error jaringan
                });
            }
        }

        function markAsCompleted(pesananId) { // Mendefinisikan fungsi markAsCompleted
            if (confirm('Anda yakin ingin menandai pesanan ' + pesananId + ' sebagai SELESAI?')) { // Konfirmasi penyelesaian pesanan
                fetch('update_pesanan_status_seller.php', { // Memanggil skrip update_pesanan_status_seller.php
                    method: 'POST', // Menggunakan metode POST
                    headers: {
                        'Content-Type': 'application/json', // Mengatur header Content-Type
                    },
                    body: JSON.stringify({
                        pesanan_id: pesananId, // ID pesanan
                        status: 'selesai' // Mengatur status pesanan menjadi 'selesai'
                    })
                })
                .then(response => response.json()) // Menguraikan respons JSON
                .then(data => {
                    if (data.success) { // Memeriksa apakah pembaruan status berhasil
                        alert('Pesanan ' + pesananId + ' berhasil ditandai sebagai selesai.'); // Menampilkan pesan sukses
                        location.reload(); // Memuat ulang halaman
                    } else {
                        alert('Gagal menandai pesanan sebagai selesai: ' + data.message); // Menampilkan pesan error
                    }
                })
                .catch(error => {
                    console.error('Error:', error); // Log error
                    alert('Terjadi kesalahan jaringan.'); // Menampilkan pesan error jaringan
                });
            }
        }
    </script>
</body>
</html>