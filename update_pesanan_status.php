<?php
session_start(); // Memulai sesi PHP
include 'db_connection.php'; // Menyertakan file koneksi database

header('Content-Type: application/json'); // Mengatur header respons sebagai JSON

$response = ['success' => false, 'message' => '']; // Inisialisasi array respons

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'seller') { // Memeriksa apakah pengguna sudah login dan role-nya adalah seller
    $response['message'] = 'Unauthorized or not a seller.'; // Mengatur pesan error jika tidak terotorisasi atau bukan seller
    echo json_encode($response); // Mengembalikan respons JSON
    exit; // Menghentikan eksekusi skrip
}

// Mengambil data JSON dari request body
$input = json_decode(file_get_contents('php://input'), true);
$pesanan_id = intval($input['pesanan_id'] ?? 0); // Mengambil dan membersihkan pesanan_id
$new_status = $conn->real_escape_string($input['status'] ?? ''); // Mengambil dan membersihkan status baru
$seller_id = intval($_SESSION['id']); // Mengambil ID seller dari sesi

// Memastikan status baru valid untuk penjual
$allowed_statuses = ['dikirim', 'selesai']; // Status yang diizinkan untuk diatur oleh penjual
if (!in_array($new_status, $allowed_statuses)) { // Memeriksa apakah status baru tidak ada dalam daftar status yang diizinkan
    $response['message'] = 'Invalid status provided.'; // Mengatur pesan error jika status tidak valid
    echo json_encode($response); // Mengembalikan respons JSON
    exit; // Menghentikan eksekusi skrip
}

if ($pesanan_id > 0) { // Memeriksa validitas pesanan_id
    // Untuk memperbarui status pesanan, kita perlu memastikan pesanan ini berisi setidaknya satu produk yang dimiliki oleh penjual ini.
    // Ini adalah pemeriksaan keamanan tambahan.
    $check_ownership_stmt = $conn->prepare("
        SELECT COUNT(pd.pesanan_id) as total_products
        FROM pesanandetail pd
        JOIN produk p ON pd.produk_id = p.produk_id
        WHERE pd.pesanan_id = ? AND p.seller_id = ?
    ");
    if ($check_ownership_stmt) { // Memeriksa apakah pernyataan berhasil disiapkan
        $check_ownership_stmt->bind_param("ii", $pesanan_id, $seller_id); // Mengikat parameter ke pernyataan
        $check_ownership_stmt->execute(); // Mengeksekusi pernyataan
        $ownership_result = $check_ownership_stmt->get_result()->fetch_assoc(); // Mengambil hasil
        $check_ownership_stmt->close(); // Menutup pernyataan

        if ($ownership_result['total_products'] > 0) { // Memeriksa apakah ada produk yang dimiliki oleh penjual
            // Mempersiapkan pernyataan SQL untuk memperbarui status pesanan
            $stmt = $conn->prepare("UPDATE pesanan SET status = ? WHERE pesanan_id = ?");
            if ($stmt) { // Memeriksa apakah pernyataan berhasil disiapkan
                $stmt->bind_param("si", $new_status, $pesanan_id); // Mengikat parameter ke pernyataan
                if ($stmt->execute()) { // Mengeksekusi pernyataan
                    $response['success'] = true; // Mengatur success menjadi true
                    $response['message'] = 'Status pesanan berhasil diperbarui menjadi ' . $new_status . '.'; // Mengatur pesan sukses
                } else {
                    $response['message'] = 'Gagal memperbarui status pesanan di database: ' . $stmt->error; // Mengatur pesan error jika eksekusi gagal
                }
                $stmt->close(); // Menutup pernyataan
            } else {
                $response['message'] = 'Gagal menyiapkan statement update: ' . $conn->error; // Mengatur pesan error jika persiapan pernyataan gagal
            }
        } else {
            $response['message'] = 'Pesanan tidak ditemukan atau Anda tidak memiliki izin untuk mengubah status pesanan ini.'; // Mengatur pesan error jika penjual tidak memiliki izin
        }
    } else {
        $response['message'] = 'Gagal menyiapkan statement pengecekan kepemilikan: ' . $conn->error; // Mengatur pesan error jika persiapan pernyataan pengecekan kepemilikan gagal
    }
} else {
    $response['message'] = 'ID Pesanan tidak valid.'; // Mengatur pesan error jika ID pesanan tidak valid
}

echo json_encode($response); // Mengembalikan respons JSON
?>