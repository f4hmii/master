<?php
session_start();
header('Content-Type: application/json');
include '../db_connection.php'; // Sesuaikan path jika berbeda

if (!isset($_SESSION['id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$pengguna_id = intval($_SESSION['id']);
$role = $_SESSION['role'] ?? '';

$data = [];

if ($role === 'seller') {
    $stmt = $conn->prepare("
      SELECT
            ps.pesanan_id,
            ps.status,
            ps.tanggal_pesan,
            p.nama_produk,
            pd.quantity,
            pd.harga_produk,
            pd.color,
            pd.size,
            p.foto_url
        FROM pesanandetail pd
        JOIN produk p ON pd.produk_id = p.produk_id
        JOIN pesanan ps ON pd.pesanan_id = ps.pesanan_id
        WHERE p.seller_id = ? AND (ps.status = 'dibayar' OR ps.status = 'diproses_penjual')
        ORDER BY ps.tanggal_pesan DESC, p.nama_produk ASC
    $stmt->bind_param("i", $pengguna_id);
} else if ($role === 'buyer') {
    $stmt = $conn->prepare("
        SELECT pesanan_id, status, tanggal_pesanan
        FROM pesanan
        WHERE buyer_id = ? AND (status = 'tertunda_pembayaran' OR status = 'diproses_penjual')
        ORDER BY tanggal_pesanan DESC
    ");
    $stmt->bind_param("i", $pengguna_id);
} else {
    echo json_encode(['error' => 'Invalid role']);
    exit;
}

$stmt->execute();
$result = $stmt->get_result();

$grouped_orders = []; // Inisialisasi array untuk pesanan yang dikelompokkan
while ($row = $result->fetch_assoc()) { // Melakukan iterasi pada setiap baris hasil query
    $pesanan_id = $row['pesanan_id']; // Mengambil ID pesanan

    if (!isset($grouped_orders[$pesanan_id])) { // Memeriksa apakah pesanan_id sudah ada di grouped_orders
        $grouped_orders[$pesanan_id] = [ // Jika belum, inisialisasi entri baru
            'pesanan_id' => $pesanan_id, // ID pesanan
            'status' => $row['status'], // Status pesanan
            'tanggal_pesan' => $row['tanggal_pesan'], // Tanggal pesanan
            'produk' => [] // Array kosong untuk produk
        ];
    }
    // Menambahkan detail produk ke pesanan yang sesuai
    $grouped_orders[$pesanan_id]['produk'][] = [
        'nama_produk' => $row['nama_produk'], // Nama produk
        'quantity' => $row['quantity'], // Kuantitas produk
        'harga_produk' => $row['harga_produk'], // Harga produk
        'color' => $row['color'], // Warna produk
        'size' => $row['size'], // Ukuran produk
        'foto_url' => $row['foto_url'] // URL foto produk
    ];
}

$data = array_values($grouped_orders); // Mengambil nilai dari grouped_orders menjadi array data

$stmt->close();
echo json_encode($data);
