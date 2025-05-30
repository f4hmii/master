<!-- <?php
include '../db_connection.php';

$kategoriList = $conn->query("SELECT * FROM kategori");

if (isset($_POST['tambah'])) {
    $nama = $_POST['nama_kategori'];
    $conn->query("INSERT INTO kategori (nama_kategori) VALUES ('$nama')");
    header("Location: kategori_admin.php");
}

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $conn->query("DELETE FROM kategori WHERE kategori_id = $id");
    header("Location: kategori_admin.php");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola Kategori</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">
    <h2 class="mb-4">Kelola Kategori</h2>

    <form method="POST" class="mb-3 d-flex gap-2">
        <input type="text" name="nama_kategori" class="form-control" placeholder="Nama kategori baru" required>
        <button type="submit" name="tambah" class="btn btn-primary">Tambah</button>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nama Kategori</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($kategori = $kategoriList->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($kategori['nama_kategori']) ?></td>
                <td>
                    <a href="?hapus=<?= $kategori['kategori_id'] ?>" onclick="return confirm('Hapus kategori ini?')" class="btn btn-danger btn-sm">Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html> -->
