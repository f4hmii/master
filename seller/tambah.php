<?php
session_start();
include '../db_connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'seller') {
    header("Location: ../login.php");
    exit;
}
if (!isset($_SESSION['id'])) {
    die("Error: Anda belum login atau session pengguna tidak ditemukan.");
}
$pengguna_id = intval($_SESSION['id']);

if (isset($_POST['simpan'])) {
    $nama        = $conn->real_escape_string(htmlspecialchars($_POST['nama']));
    $deskripsi   = $conn->real_escape_string(htmlspecialchars($_POST['deskripsi']));
    $harga       = floatval($_POST['harga']);
    $kategori_id = intval($_POST['kategori_id']);
    $kondisi     = isset($_POST['kondisi']) ? $conn->real_escape_string($_POST['kondisi']) : '';

    // Stok keseluruhan
    $stok_keseluruhan = intval($_POST['stok_keseluruhan']);

    // Warna + stok per warna
    $color_names = $_POST['color_name'] ?? [];
    $color_stocks = $_POST['color_stock'] ?? [];
    $color_stock_array = [];
    foreach ($color_names as $idx => $color) {
        $color_trim = trim($color);
        $stok_warna = intval($color_stocks[$idx] ?? 0);
        if ($color_trim !== '') {
            $color_stock_array[$color_trim] = $stok_warna;
        }
    }
    $color_stock_json = json_encode($color_stock_array);

    // Ukuran + stok per ukuran
    $size_names = $_POST['size_name'] ?? [];
    $size_stocks = $_POST['size_stock'] ?? [];
    $size_stock_array = [];
    foreach ($size_names as $idx => $size) {
        $size_trim = trim($size);
        $stok_ukuran = intval($size_stocks[$idx] ?? 0);
        if ($size_trim !== '') {
            $size_stock_array[$size_trim] = $stok_ukuran;
        }
    }
    $size_stock_json = json_encode($size_stock_array);

    $upload_dir = "../uploads/";
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

    // Upload gambar utama
    $gambar     = $_FILES['gambar']['name'];
    $tmp_name   = $_FILES['gambar']['tmp_name'];
    $file_ext   = strtolower(pathinfo($gambar, PATHINFO_EXTENSION));
    $new_filename = '';

    if (in_array($file_ext, $allowed_ext)) {
        $new_filename = time() . '_' . uniqid() . '.' . $file_ext;
        $target_file = $upload_dir . $new_filename;
        if (!move_uploaded_file($tmp_name, $target_file)) {
            echo "<div class='alert alert-danger'>Gagal mengupload gambar utama.</div>";
            exit;
        }
    } else {
        echo "<div class='alert alert-warning'>Format gambar utama tidak diizinkan.</div>";
        exit;
    }

    // Simpan produk
    $sql = "INSERT INTO produk (nama_produk, deskripsi, stock, harga, foto_url, seller_id, kategori_id, color_stock, size_stock, kondisi, verified) 
            VALUES ('$nama', '$deskripsi', $stok_keseluruhan, $harga, '$new_filename', $pengguna_id, $kategori_id, ?, ?, '$kondisi', 0)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $color_stock_json, $size_stock_json);

    if ($stmt->execute()) {
        $produk_id = $stmt->insert_id;

        // Upload foto detail produk jika ada
        if (!empty($_FILES['foto_detail']['name'][0])) {
            foreach ($_FILES['foto_detail']['name'] as $key => $filename) {
                $tmp_name = $_FILES['foto_detail']['tmp_name'][$key];
                $error = $_FILES['foto_detail']['error'][$key];
                $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                if ($error === UPLOAD_ERR_OK && in_array($file_ext, $allowed_ext)) {
                    $new_file_detail = time() . '_' . uniqid() . '.' . $file_ext;
                    $target_file_detail = $upload_dir . $new_file_detail;

                    if (move_uploaded_file($tmp_name, $target_file_detail)) {
                        $stmt2 = $conn->prepare("INSERT INTO produk_foto_detail (produk_id, foto_path) VALUES (?, ?)");
                        $stmt2->bind_param("is", $produk_id, $new_file_detail);
                        $stmt2->execute();
                        $stmt2->close();
                    } else {
                        echo "<div class='alert alert-warning'>Gagal upload file detail: $filename</div>";
                    }
                } else {
                    echo "<div class='alert alert-warning'>File detail tidak valid atau format tidak didukung: $filename</div>";
                }
            }
        }

        echo "<div class='alert alert-success'>Produk berhasil ditambahkan dan menunggu verifikasi admin.</div>";
        echo "<script>setTimeout(() => { window.location.href = 'produk.php'; }, 1500);</script>";
    } else {
        echo "<div class='alert alert-danger'>Gagal menyimpan produk: " . $stmt->error . "</div>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Tambah Produk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">

    <h2>Tambah Produk</h2>

    <form method="POST" enctype="multipart/form-data">

        <div class="mb-3">
            <label>Nama Produk</label>
            <input type="text" name="nama" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Deskripsi</label>
            <textarea name="deskripsi" class="form-control" required></textarea>
        </div>

        <div class="mb-3">
            <label>Harga</label>
            <input type="number" name="harga" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Stok Keseluruhan</label>
            <input type="number" name="stok_keseluruhan" class="form-control" min="0" required>
        </div>

        <div class="mb-3">
            <label>Gambar Utama</label>
            <input type="file" name="gambar" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Gambar Detail Produk (bisa banyak)</label>
            <input type="file" name="foto_detail[]" class="form-control" multiple>
        </div>

        <div class="mb-3">
            <label>Kategori</label>
            <select name="kategori_id" class="form-control" required>
                <option value="">-- Pilih Kategori --</option>
                <?php
                $kategori = $conn->query("SELECT * FROM kategori");
                while ($row = $kategori->fetch_assoc()) {
                    echo "<option value='{$row['kategori_id']}'>{$row['nama_kategori']}</option>";
                }
                ?>
            </select>
        </div>

        <!-- Warna dan stok per warna -->
        <div class="mb-3">
            <label>Warna dan Stok per Warna</label>
            <div id="colorStockContainer">
                <div class="d-flex mb-2 gap-2">
                    <input type="text" name="color_name[]" class="form-control" placeholder="Nama Warna" required>
                    <input type="number" name="color_stock[]" class="form-control" placeholder="Stok Warna" min="0" required>
                    <button type="button" class="btn btn-danger" onclick="removeRow(this)">Hapus</button>
                </div>
            </div>
            <button type="button" class="btn btn-primary mt-2" onclick="addColorRow()">Tambah Warna</button>
        </div>

        <!-- Ukuran dan stok per ukuran -->
        <div class="mb-3">
            <label>Ukuran dan Stok per Ukuran</label>
            <div id="sizeStockContainer">
                <div class="d-flex mb-2 gap-2">
                    <input type="text" name="size_name[]" class="form-control" placeholder="Ukuran (misal S, M, L)" required>
                    <input type="number" name="size_stock[]" class="form-control" placeholder="Stok Ukuran" min="0" required>
                    <button type="button" class="btn btn-danger" onclick="removeRow(this)">Hapus</button>
                </div>
            </div>
            <button type="button" class="btn btn-primary mt-2" onclick="addSizeRow()">Tambah Ukuran</button>
        </div>

        <div class="mb-3">
            <label>Kondisi Produk</label>
            <select name="kondisi" class="form-control" required>
                <option value="">-- Pilih Kondisi --</option>
                <option value="Baru">Baru</option>
                <option value="Bekas">Bekas</option>
            </select>
        </div>

        <button class="btn btn-success" name="simpan">Simpan</button>
        <a href="produk.php" class="btn btn-secondary">Kembali</a>

    </form>

<script>
function addColorRow() {
    const container = document.getElementById('colorStockContainer');
    const div = document.createElement('div');
    div.className = 'd-flex mb-2 gap-2';
    div.innerHTML = `
        <input type="text" name="color_name[]" class="form-control" placeholder="Nama Warna" required>
        <input type="number" name="color_stock[]" class="form-control" placeholder="Stok Warna" min="0" required>
        <button type="button" class="btn btn-danger" onclick="removeRow(this)">Hapus</button>
    `;
    container.appendChild(div);
}

function addSizeRow() {
    const container = document.getElementById('sizeStockContainer');
    const div = document.createElement('div');
    div.className = 'd-flex mb-2 gap-2';
    div.innerHTML = `
        <input type="text" name="size_name[]" class="form-control" placeholder="Ukuran (misal S, M, L)" required>
        <input type="number" name="size_stock[]" class="form-control" placeholder="Stok Ukuran" min="0" required>
        <button type="button" class="btn btn-danger" onclick="removeRow(this)">Hapus</button>
    `;
    container.appendChild(div);
}

function removeRow(button) {
    button.parentElement.remove();
}
</script>

</body>
</html>
