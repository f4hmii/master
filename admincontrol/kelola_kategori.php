<?php
// Koneksi ke database
include '../db_connection.php';

// Handle pesan
session_start();
$pesan = '';
if (isset($_SESSION['pesan'])) {
    $pesan = "<div class='alert alert-success mb-2'>" . $_SESSION['pesan'] . "</div>";
    unset($_SESSION['pesan']); // Hapus setelah ditampilkan agar tidak muncul terus
}

// Ambil semua kategori dan jumlah produk
$kategoriList = $conn->query("
    SELECT k.kategori_id, k.nama_kategori, COUNT(p.produk_id) AS jumlah_produk
    FROM kategori k
    LEFT JOIN produk p ON k.kategori_id = p.kategori_id
    GROUP BY k.kategori_id
");

// Ambil semua produk
$produkResult = $conn->query("
    SELECT 
        p.produk_id, p.nama_produk, p.deskripsi, p.stock, p.harga, p.foto_url,
        k.nama_kategori
    FROM produk p
    LEFT JOIN kategori k ON p.kategori_id = k.kategori_id
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola Kategori Produk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <!-- Sidebar -->
    <h2 class="mb-4">Kelola Kategori Produk</h2>

     <!-- Tampilkan pesan -->
    <?php if ($pesan) echo $pesan; ?>
    
    <!-- Form Tambah Kategori -->
    <form method="POST" action="simpan_kategori.php" class="mb-4 d-flex gap-2">
        <input type="text" name="nama_kategori" class="form-control" placeholder="Nama Kategori" required>
        <button type="submit" name="tambah_kategori" class="btn btn-success">Tambah Kategori</button>
    </form>

    <!-- Daftar Kategori -->
    <div class="mb-4">
        <h5>Daftar Kategori</h5>
        <ul class="list-group">
        <?php while ($kategori = $kategoriList->fetch_assoc()): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span><?= htmlspecialchars($kategori['nama_kategori']) ?></span>
                <span>
                    <a href="hapusKategori.php?kategori_id=<?= $kategori['kategori_id'] ?>"
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('Yakin ingin menghapus kategori ini?')">Hapus</a>
                    <span class="badge bg-primary rounded-pill ms-2"><?= $kategori['jumlah_produk'] ?> produk</span>
                </span>
            </li>
        <?php endwhile; ?>
        </ul>
    </div>

    <!-- Daftar Produk -->
    <h5 class="mb-3">Daftar Produk</h5>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Nama Produk</th>
                <th>Deskripsi</th>
                <th>Stok</th>
                <th>Harga</th>
                <th>Kategori</th>
                <th>Gambar</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $produkResult->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['nama_produk']) ?></td>
                    <td><?= htmlspecialchars($row['deskripsi']) ?></td>
                    <td><?= $row['stock'] ?></td>
                    <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                    <td><?= $row['nama_kategori'] ?? '<em>Belum Ditentukan</em>' ?></td>
                    <td>
                        <?php if ($row['foto_url']): ?>
                            <img src="../uploads/<?= $row['foto_url'] ?>" width="60" height="60" style="object-fit:cover;">
                        <?php else: ?>
                            <span class="text-muted">Tidak Ada</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="edit_kategori_produk.php?produk_id=<?= $row['produk_id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</body>
</html>
