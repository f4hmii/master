<?php
session_start();
include '../db_connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$action = $_GET['action'] ?? '';
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: verifikasi_produk.php");
    exit;
}

// Ambil info produk + penjual
$produk = $conn->query("
    SELECT p.*, u.email as seller_email, u.nama_pengguna as seller_name
    FROM produk p
    JOIN pengguna u ON p.seller_id = u.pengguna_id
    WHERE p.produk_id = $id
");

if (!$produk || $produk->num_rows == 0) {
    $_SESSION['error'] = "Produk tidak ditemukan";
    header("Location: verifikasi_produk.php");
    exit;
}

$produk_data = $produk->fetch_assoc();

if ($action === 'approve') {
    $updateQuery = "UPDATE produk SET verified = 1 WHERE produk_id = $id";
    if (!$conn->query($updateQuery)) {
        $_SESSION['error'] = "Gagal update produk: " . $conn->error;
    } else {
        // Kirim email notifikasi
        $to = $produk_data['seller_email'];
        $subject = "Produk Anda Telah Disetujui";
        $message = "Halo " . $produk_data['seller_name'] . ",\n\n";
        $message .= "Produk Anda '" . $produk_data['nama_produk'] . "' telah disetujui oleh admin dan sekarang sudah muncul di toko online.\n\n";
        $message .= "Terima kasih,\nTim Admin";
        $headers = "From: admin@tokoonline.com";

        mail($to, $subject, $message, $headers);

        $_SESSION['success'] = "Produk berhasil disetujui dan sekarang sudah muncul di toko online";
    }
} elseif ($action === 'reject') {
    if (!$conn->query("DELETE FROM produk WHERE produk_id = $id")) {
        $_SESSION['error'] = "Gagal menghapus produk: " . $conn->error;
    } else {
        $to = $produk_data['seller_email'];
        $subject = "Produk Anda Ditolak";
        $message = "Halo " . $produk_data['seller_name'] . ",\n\n";
        $message .= "Maaf, produk Anda '" . $produk_data['nama_produk'] . "' tidak memenuhi syarat untuk dijual di toko kami.\n\n";
        $message .= "Silakan periksa kembali produk Anda atau hubungi admin untuk informasi lebih lanjut.\n\n";
        $message .= "Terima kasih,\nTim Admin";
        $headers = "From: admin@tokoonline.com";

        mail($to, $subject, $message, $headers);

        $_SESSION['success'] = "Produk berhasil ditolak dan dihapus dari sistem";
    }
} else {
    $_SESSION['error'] = "Aksi tidak valid";
}

header("Location: verifikasi_produk.php");
exit;
?>
