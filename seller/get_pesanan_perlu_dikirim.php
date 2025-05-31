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
        SELECT DISTINCT ps.pesanan_id, ps.status, ps.tanggal_pesanan
        FROM pesanandetail pd
        JOIN produk p ON pd.produk_id = p.produk_id
        JOIN pesanan ps ON pd.pesanan_id = ps.pesanan_id
        WHERE p.seller_id = ? AND (ps.status = 'dibayar' OR ps.status = 'diproses_penjual')
        ORDER BY ps.tanggal_pesanan DESC
    ");
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

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

$stmt->close();
echo json_encode($data);
