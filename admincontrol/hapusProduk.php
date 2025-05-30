<?php
include '../db_connection.php';

// Periksa apakah parameter produk_id tersedia dan valid (harus angka)
if (isset($_GET['produk_id']) && is_numeric($_GET['produk_id'])) {
    $id = intval($_GET['produk_id']); // konversi ke integer

    // Gunakan prepared statement untuk keamanan
    $stmt = $conn->prepare("DELETE FROM produk WHERE produk_id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: dashbord_admin.php#kelola_produk");
        exit();
    } else {
        echo "Gagal menghapus produk.";
    }

    $stmt->close();
} else {
    echo "ID produk tidak ditemukan atau tidak valid.";
}
?>