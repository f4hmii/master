<?php
include '../db_connection.php';
$id = intval($_GET['pengguna_id']);
$data = $conn->query("SELECT * FROM pengguna WHERE pengguna_id=$id")->fetch_assoc();
?>
<?php
if (isset($_POST['update'])) {
    $username      = $_POST['username'];
    $nama_pengguna = $_POST['nama_pengguna'];
    $email         = $_POST['email'];
    $telepon       = $_POST['telepon'];
    $alamat        = $_POST['alamat'];
    $role          = $_POST['role'];

    $query = "UPDATE pengguna SET 
        username='$username',
        nama_pengguna='$nama_pengguna',
        email='$email',
        nomor_telepon='$telepon',
        alamat='$alamat',
        role='$role'
        WHERE pengguna_id=$id";

    $result = $conn->query($query);

    if ($result) {
        header("Location: dashbord_admin.php#kelola_user");
        exit;
    } else {
        echo "<div class='alert alert-danger'>Gagal update: " . $conn->error . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <h2>Edit User</h2>
    <form method="POST">
        <div class="mb-3">
            <label>Username</label>
            <input type="text" name="username" value="<?= htmlspecialchars($data['username']) ?>" class="form-control">
        </div>
        <div class="mb-3">
            <label>Nama Pengguna</label>
            <input type="text" name="nama_pengguna" value="<?= htmlspecialchars($data['nama_pengguna']) ?>" class="form-control">
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($data['email']) ?>" class="form-control">
        </div>
        <div class="mb-3">
            <label>Telepon</label>
            <input type="text" name="telepon" value="<?= htmlspecialchars($data['nomor_telepon']) ?>" class="form-control">
        </div>
        <div class="mb-3">
            <label>Alamat</label>
            <textarea name="alamat" class="form-control"><?= htmlspecialchars($data['alamat']) ?></textarea>
        </div>
        <div class="mb-3">
            <label>Role</label>
            <select name="role" class="form-control">
                <option value="seller" <?= $data['role'] == 'seller' ? 'selected' : '' ?>>Seller</option>
                <option value="buyer" <?= $data['role'] == 'buyer' ? 'selected' : '' ?>>Buyer</option>
            </select>
        </div>

        <button class="btn btn-primary" name="update">Update</button>
        <a href="dashbord_admin.php#kelola_user" class="btn btn-secondary">Kembali</a>
    </form>


</body>
</html>
