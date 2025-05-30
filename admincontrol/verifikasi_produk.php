<?php
session_start();
include '../db_connection.php';

// Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$per_page = 10;
$page = isset($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$search_condition = $search ? "AND (p.nama_produk LIKE '%$search%' OR p.deskripsi LIKE '%$search%')" : '';

// Ambil total produk belum terverifikasi untuk pagination
$total_query = $conn->query("SELECT COUNT(*) as total FROM produk p WHERE p.verified = 0 $search_condition");
$total_rows = $total_query->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $per_page);

// Ambil data produk yang belum terverifikasi
$produk = $conn->query("
    SELECT p.*, u.nama_pengguna AS seller_name
    FROM produk p
    JOIN pengguna u ON p.seller_id = u.pengguna_id
    WHERE p.verified = 0 $search_condition
    ORDER BY p.produk_id DESC
    LIMIT $offset, $per_page
");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Verifikasi Produk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <h2>Produk Menunggu Verifikasi</h2>

    <?php
    // Tampilkan pesan sukses/error
    if (isset($_SESSION['success'])) {
        echo "<div class='alert alert-success'>" . $_SESSION['success'] . "</div>";
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        echo "<div class='alert alert-danger'>" . $_SESSION['error'] . "</div>";
        unset($_SESSION['error']);
    }
    ?>

    <form method="GET" class="d-flex mb-3">
        <input type="text" name="search" class="form-control me-2" placeholder="Cari produk..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-primary">Cari</button>
    </form>

    <?php if ($produk && $produk->num_rows > 0): ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nama Produk</th>
                    <th>Gambar</th>
                    <th>Deskripsi</th>
                    <th>Stok</th>
                    <th>Harga</th>
                    <th>Penjual</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $produk->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['produk_id'] ?></td>
                    <td><?= htmlspecialchars($row['nama_produk']) ?></td>
                    <td><img src="../uploads/<?= htmlspecialchars($row['foto_url']) ?>" width="80" class="img-thumbnail"></td>
                    <td><?= nl2br(htmlspecialchars(substr($row['deskripsi'], 0, 100))) ?><?= strlen($row['deskripsi']) > 100 ? '...' : '' ?></td>
                    <td><?= $row['stock'] ?></td>
                    <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                    <td><?= htmlspecialchars($row['seller_name']) ?></td>
                    <td>
                        <a href="verifikasi_detail.php?id=<?= $row['produk_id'] ?>" class="btn btn-sm btn-info">Detail</a>
                        <a href="verifikasi_aksi.php?action=approve&id=<?= $row['produk_id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Setujui produk ini?')">Setujui</a>
                        <a href="verifikasi_aksi.php?action=reject&id=<?= $row['produk_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tolak produk ini?')">Tolak</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <nav>
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>">Previous</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <?php else: ?>
        <div class="alert alert-info">Tidak ada produk yang menunggu verifikasi</div>
    <?php endif; ?>
</body>
</html>
