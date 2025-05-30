<?php
include '../db_connection.php';

if (isset($_GET['produk_id']) && is_numeric($_GET['produk_id'])) {
    $id = intval($_GET['produk_id']);

    // Hapus dulu data di pesanan_detail yang terkait produk ini
    $stmt0 = $conn->prepare("DELETE FROM pesanan_detail WHERE produk_id = ?");
    $stmt0->bind_param("i", $id);
    $stmt0->execute();
    $stmt0->close();

    // Hapus dulu data ukuran terkait
    $stmt1 = $conn->prepare("DELETE FROM produk_size WHERE produk_id = ?");
    $stmt1->bind_param("i", $id);
    $stmt1->execute();
    $stmt1->close();

    // Baru hapus produk
    $stmt2 = $conn->prepare("DELETE FROM produk WHERE produk_id = ?");
    $stmt2->bind_param("i", $id);

    if ($stmt2->execute()) {
        header("Location: produk.php");
        exit();
    } else {
        echo "Gagal menghapus produk.";
    }
    $stmt2->close();

} else {
    echo "ID produk tidak ditemukan atau tidak valid.";
}
?>
