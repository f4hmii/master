<?php
include '../db_connection.php';

$id = $_GET['produk_id'];
$produk = $conn->query("SELECT * FROM produk WHERE produk_id = $id")->fetch_assoc();
$kategoriList = $conn->query("SELECT * FROM kategori");


if (isset($_POST['update'])) {
    $nama = $_POST['nama_produk'];
    $deskripsi = $_POST['deskripsi'];
    $stock = $_POST['stock'];
    $harga = $_POST['harga'];
    $kategori_id = $_POST['kategori_id'] ?: "NULL";

    $conn->query("UPDATE produk SET 
        nama_produk = '$nama',
        deskripsi = '$deskripsi',
        stock = $stock,
        harga = $harga,
        kategori_id = $kategori_id
        WHERE produk_id = $id
    ");

    header("Location: dashbord_admin.php#kelola_kategori");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Kategori Produk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">
    <h2>Edit Produk & Kategori</h2>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label>Nama Produk</label>
            <input type="text" name="nama_produk" class="form-control" value="<?= $produk['nama_produk'] ?>" required>
        </div>
        <div class="mb-3">
            <label>Deskripsi</label>
            <textarea name="deskripsi" class="form-control"><?= $produk['deskripsi'] ?></textarea>
        </div>
        <div class="mb-3">
            <label>Stok</label>
            <input type="number" name="stock" class="form-control" value="<?= $produk['stock'] ?>">
        </div>
        <div class="mb-3">
            <label>Harga</label>
            <input type="number" name="harga" class="form-control" value="<?= $produk['harga'] ?>">
        </div>
        <div class="mb-3">
            <label>Kategori</label>
            <select name="kategori_id" class="form-control">
                <option value="">-- Pilih Kategori --</option>
                <?php while ($kategori = $kategoriList->fetch_assoc()): ?>
                    <option value="<?= $kategori['kategori_id'] ?>" <?= $kategori['kategori_id'] == $produk['kategori_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($kategori['nama_kategori']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <button class="btn btn-primary" name="update">Update</button>
        <a href="dashbord_admin.php#kelola_kategori" class="btn btn-secondary">Kembali</a>
    </form>


</body>
</html>
