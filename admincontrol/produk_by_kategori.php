<?php
include '../db_connection.php';

$kategori_id = isset($_GET['kategori_id']) ? intval($_GET['kategori_id']) : 0;

// Ambil nama kategori
$kategoriResult = $conn->query("SELECT nama_kategori FROM kategori WHERE kategori_id = $kategori_id");
$kategori = $kategoriResult->fetch_assoc();

// Ambil semua produk dari kategori ini
$produkResult = $conn->query("
    SELECT * FROM produk 
    WHERE kategori_id = $kategori_id
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Produk: <?= htmlspecialchars($kategori['nama_kategori'] ?? 'Kategori Tidak Ditemukan') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card-img-top {
            height: 180px;
            object-fit: cover;
        }
    </style>
</head>
<body class="container py-5">
    <h2 class="mb-4">Produk Kategori: <?= htmlspecialchars($kategori['nama_kategori'] ?? 'Kategori Tidak Ditemukan') ?></h2>

    <a href="kelola_kategori.php" class="btn btn-secondary mb-4">⬅️ Kembali ke Daftar Kategori</a>

    <?php if ($produkResult->num_rows > 0): ?>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php while ($produk = $produkResult->fetch_assoc()): ?>
                <div class="col">
                    <div class="card h-100">
                        <?php if (!empty($produk['foto_url'])): ?>
                            <img src="../uploads/<?= $produk['foto_url'] ?>" class="card-img-top" alt="gambar produk">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/300x180?text=No+Image" class="card-img-top" alt="tidak ada gambar">
                        <?php endif; ?>

                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($produk['nama_produk']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($produk['deskripsi']) ?></p>
                            <p class="mb-1"><strong>Stok:</strong> <?= $produk['stock'] ?></p>
                            <p><strong>Harga:</strong> Rp <?= number_format($produk['harga'], 0, ',', '.') ?></p>
                        </div>
                        <div class="card-footer text-end">
                            <a href="edit_kategori_produk.php?produk_id=<?= $produk['produk_id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">Belum ada produk dalam kategori ini.</div>
    <?php endif; ?>
</body>
</html>
