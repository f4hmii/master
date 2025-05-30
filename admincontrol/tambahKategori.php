<?php
include '../db_connection.php';

if (isset($_POST['submit'])) {
    $nama = $_POST['nama_kategori'];
    $conn->query("INSERT INTO kategori(nama_kategori) VALUES ('$nama')");
    header("Location: kelola_kategori.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Tambah Kategori</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <h2>Tambah Kategori</h2>
    <form method="post">
        <div class="mb-3">
            <label>Nama Kategori</label>
            <input type="text" name="nama_kategori" class="form-control" required>
        </div>
        <button name="submit" class="btn btn-primary">Tambah</button>
        <a href="kelola_kategori.php" class="btn btn-secondary">Kembali</a>
    </form>
</body>
</html>
