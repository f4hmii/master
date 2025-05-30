<?php
include '../db_connection.php';

// Ambil data produk
$result = mysqli_query($conn, "SELECT 
    p.produk_id,
    p.foto_url, 
    pa.nama_pengguna, 
    p.nama_produk, 
    p.deskripsi, 
    p.stock,
    k.nama_kategori,
    p.harga FROM produk p
    LEFT JOIN kategori k ON p.kategori_id = k.kategori_id
    LEFT JOIN pengguna pa ON p.seller_id = pa.pengguna_id 
    ORDER BY p.produk_id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kelola Produk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Kelola Produk</h2>
    <div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>No</th>
                <th>Gambar</th>
                <th>Toko</th>
                <th>Nama Produk</th>
                <th>Deskripsi</th>
                <th>Stok</th>
                <th>Kategori</th>
                <th>Harga</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $no = 1;
        while ($row = mysqli_fetch_assoc($result)) {
        ?>
            <tr>
                <td><?= $no++ ?></td>
                <td>
                    <img src="../uploads/<?= htmlspecialchars($row['foto_url']) ?>" width="80" height="80" class="img-thumbnail" alt="gambar produk">
                </td>
                <td><?= htmlspecialchars($row['nama_pengguna']) ?></td>
                <td><?= htmlspecialchars($row['nama_produk']) ?></td>
                <td><?= htmlspecialchars($row['deskripsi']) ?></td>
                <td><?= htmlspecialchars($row['stock']) ?></td>
                <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                <td>Rp<?= number_format($row['harga'], 0, ',', '.') ?></td>
                <td>
                    <a href="editProduk.php?produk_id=<?= $row['produk_id'] ?>" class="btn btn-warning btn-sm mb-1">Edit</a>
                    <a href="hapusProduk.php?produk_id=<?= $row['produk_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                </td>
            </tr>
        
        <?php } ?>
        </tbody>
    </table>
    </div>
</div>
</body>
</html>