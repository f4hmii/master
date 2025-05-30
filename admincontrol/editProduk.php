<?php
include '../db_connection.php';

$id = intval($_GET['produk_id']);
$data = $conn->query("SELECT * FROM produk WHERE produk_id=$id")->fetch_assoc();

if (isset($_POST['update'])) {
    $nama       = $conn->real_escape_string($_POST['nama']);
    $deskripsi  = $conn->real_escape_string($_POST['deskripsi']);
    $stok       = intval($_POST['stok']);
    $harga      = floatval($_POST['harga']);

    // Handle upload gambar baru
    if (!empty($_FILES['gambar']['name'])) {
        $gambar = $_FILES['gambar']['name'];
        $tmp    = $_FILES['gambar']['tmp_name'];
        $upload_folder = "../uploads/";

        $ext = pathinfo($gambar, PATHINFO_EXTENSION);
        $new_gambar = time() . '_' . uniqid() . '.' . $ext;

        if (move_uploaded_file($tmp, $upload_folder . $new_gambar)) {
            // Hapus gambar lama jika ada
            if ($data['foto_url'] && file_exists($upload_folder . $data['foto_url'])) {
                unlink($upload_folder . $data['foto_url']);
            }
            $gambar_final = $new_gambar;
        } else {
            echo "<div class='alert alert-danger'>Gagal mengupload gambar baru.</div>";
            exit;
        }
    } else {
        $gambar_final = $data['foto_url'];
    }

    $sql_update = "UPDATE produk SET 
        nama_produk='$nama', 
        deskripsi='$deskripsi', 
        stock=$stok, 
        harga=$harga, 
        foto_url='$gambar_final' 
        WHERE produk_id=$id";

    if ($conn->query($sql_update)) {
        header("Location: dashbord_admin.php#kelola_produk");
        exit;
    } else {
        echo "<div class='alert alert-danger'>Gagal update produk: " . $conn->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Produk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <h2>Edit Produk</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label>Nama</label>
            <input type="text" name="nama" value="<?= htmlspecialchars($data['nama_produk']) ?>" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Deskripsi</label>
            <textarea name="deskripsi" class="form-control" required><?= htmlspecialchars($data['deskripsi']) ?></textarea>
        </div>
        <div class="mb-3">
            <label>Stok</label>
            <input type="number" name="stok" value="<?= intval($data['stock']) ?>" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Harga</label>
            <input type="number" name="harga" value="<?= floatval($data['harga']) ?>" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Gambar</label><br>
            <img src="../uploads/<?= htmlspecialchars($data['foto_url']) ?>" width="80"><br>
            <input type="file" name="gambar" class="form-control mt-2">
        </div>
        <button class="btn btn-primary" name="update">Update</button>
        <a href="dashbord_admin.php#kelola_produk" class="btn btn-secondary">Kembali</a>
    </form>
</body>
</html>