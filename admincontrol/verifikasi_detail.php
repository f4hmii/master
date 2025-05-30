<?php
session_start();
include '../db_connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: verifikasi_produk.php");
    exit;
}

// Ambil detail produk, user, kategori
$produk = $conn->query("
    SELECT p.*, u.email as seller_email, u.nama_pengguna as seller_name, k.nama_kategori
    FROM produk p
    JOIN pengguna u ON p.seller_id = u.pengguna_id
    LEFT JOIN kategori k ON p.kategori_id = k.kategori_id
    WHERE p.produk_id = $id
");

if (!$produk || $produk->num_rows == 0) {
    header("Location: verifikasi_produk.php");
    exit;
}

$row = $produk->fetch_assoc();

// Ambil ukuran produk
$sizes = $conn->query("SELECT size FROM produk_size WHERE produk_id = $id");
$size_list = [];
while ($size = $sizes->fetch_assoc()) {
    $size_list[] = $size['size'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Detail Produk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="container py-5">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Detail Produk #<?= $row['produk_id'] ?></h4>
            <a href="verifikasi_produk.php" class="btn btn-secondary">Kembali</a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <img src="../uploads/<?= htmlspecialchars($row['foto_url']) ?>" alt="Gambar Produk" class="img-fluid rounded" />
                </div>
                <div class="col-md-8">
                    <table class="table table-bordered">
                        <tr>
                            <th>Nama Produk</th>
                            <td><?= htmlspecialchars($row['nama_produk']) ?></td>
                        </tr>
                        <tr>
                            <th>Penjual</th>
                            <td><?= htmlspecialchars($row['seller_name']) ?></td>
                        </tr>
                        <tr>
                            <th>Kategori</th>
                            <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                        </tr>
                        <tr>
                            <th>Deskripsi</th>
                            <td><?= nl2br(htmlspecialchars($row['deskripsi'])) ?></td>
                        </tr>
                        <tr>
                            <th>Stok</th>
                            <td><?= $row['stock'] ?></td>
                        </tr>
                        <tr>
                            <th>Harga</th>
                            <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <th>Warna</th>
                            <td><?= htmlspecialchars($row['color']) ?></td>
                        </tr>
                        <tr>
                            <th>Ukuran Tersedia</th>
                            <td><?= implode(', ', $size_list) ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <?= $row['verified'] ? 
                                    '<span class="badge bg-success">Terverifikasi</span>' : 
                                    '<span class="badge bg-warning text-dark">Menunggu Verifikasi</span>' ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <?php if (!$row['verified']): ?>
                <a href="verifikasi_aksi.php?action=approve&id=<?= $row['produk_id'] ?>" class="btn btn-success" onclick="return confirm('Setujui produk ini?')">Setujui</a>
                <a href="verifikasi_aksi.php?action=reject&id=<?= $row['produk_id'] ?>" class="btn btn-danger" onclick="return confirm('Tolak produk ini?')">Tolak</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
