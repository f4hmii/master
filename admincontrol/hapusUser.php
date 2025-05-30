    <?php
    include '../db_connection.php';
    // Periksa apakah parameter pengguna_id tersedia dan valid (harus angka)
    if (isset($_GET['pengguna_id']) && is_numeric($_GET['pengguna_id'])) {
        $id = intval($_GET['pengguna_id']); // konversi ke integer

        // Gunakan prepared statement untuk keamanan
        $stmt = $conn->prepare("DELETE FROM pengguna WHERE pengguna_id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
           header("Location: dashbord_admin.php#kelola_user");
            exit();
        } else {
            echo "Gagal menghapus user.";
        }

        $stmt->close();
    } else {
        echo "ID user tidak ditemukan atau tidak valid.";
    }