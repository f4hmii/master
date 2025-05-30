<?php
// Proses form jika ada data dikirim
$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Koneksi ke database
    $host = "localhost";
    $username = "root";
    $password = "";
    $dbname = "movr"; 

    $conn = new mysqli($host, $username, $password, $dbname);

    // Ambil data dari form
    $name = $_POST['name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Cek apakah email sudah terdaftar
    $cek_email = $conn->prepare("SELECT pengguna_id FROM pengguna WHERE email = ?");
    $cek_email->bind_param("s", $email);
    $cek_email->execute();
    $cek_email->store_result();

    // Cek apakah username sudah terdaftar
    $cek_user = $conn->prepare("SELECT pengguna_id FROM pengguna WHERE username = ?");
    $cek_user->bind_param("s", $username);
    $cek_user->execute();
    $cek_user->store_result();

    if ($cek_email->num_rows > 0) {
        $error = "Email sudah terdaftar.";
    } elseif ($cek_user->num_rows > 0) {
        $error = "Username sudah digunakan.";
    } else {
        // Menyimpan data ke dalam database
        $sql = "INSERT INTO pengguna (nama_pengguna, username, email, sandi, role) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $name, $username, $email, $password, $role);

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        

        if ($stmt->execute()) {
            $success = "Akun berhasil dibuat";
            header("refresh:3;url=login.php"); // Ganti dengan URL login kamu
        } else {
            $error = "Gagal menyimpan data: " . $stmt->error;
        }

        $stmt->close();
    }

    $cek_email->close();
    $cek_user->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Create Account - MOVR</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
  <div class="flex items-center justify-center min-h-screen bg-cover bg-center" style="background-image: url('imgproduk/tangga.jpg');">
    <div class="w-full max-w-sm bg-gray-800 p-6 shadow-lg rounded-xl">
      <div class="text-center mb-6">
        <h1 class="text-3xl font-bold text-red-500">MOVR</h1>
        <h2 class="text-xl text-gray-300">Create Account</h2>
      </div>

      <?php if ($success): ?>
        <div class="mb-4 p-2 bg-green-500 text-white rounded text-center"><?= $success ?></div>
      <?php elseif ($error): ?>
        <div class="mb-4 p-2 bg-red-500 text-white rounded text-center"><?= $error ?></div>
      <?php endif; ?>

      <form method="POST" action="register.php">
        <div class="mb-4">
          <label for="name" class="block text-sm text-gray-300">Name</label>
          <input type="text" id="name" name="name" required placeholder="Enter your name" class="w-full p-2 rounded-lg text-gray-800 focus:outline-none focus:ring-2 focus:ring-red-500">
        </div>

        <div class="mb-4">
          <label for="username" class="block text-sm text-gray-300">Username</label>
          <input type="text" id="username" name="username" required placeholder="Enter your username" class="w-full p-2 rounded-lg text-gray-800 focus:outline-none focus:ring-2 focus:ring-red-500">
        </div>

        <div class="mb-4">
          <label for="email" class="block text-sm text-gray-300">Email</label>
          <input type="email" id="email" name="email" required placeholder="Enter your email" class="w-full p-2 rounded-lg text-gray-800 focus:outline-none focus:ring-2 focus:ring-red-500">
        </div>

        <div class="mb-6">
          <label for="password" class="block text-sm text-gray-300">Password</label>
          <input type="password" id="password" name="password" required placeholder="Enter your password" class="w-full p-2 rounded-lg text-gray-800 focus:outline-none focus:ring-2 focus:ring-red-500">
        </div>

        <div class="flex items-center text-sm text-gray-400 mb-6">
          <div class="flex items-center me-4">
              <input id="buyer" type="radio" name="role" value="buyer" required class="w-4 h-4">
              <label for="buyer" class="ms-2 text-sm font-medium text-gray-100">Buyer</label>
          </div>
          <div class="flex items-center me-4">
              <input id="seller" type="radio" name="role" value="seller" class="w-4 h-4">
              <label for="seller" class="ms-2 text-sm font-medium text-gray-100">Seller</label>
          </div>
        </div>

        <div class="text-sm text-gray-400 mb-6 text-center">
          <a href="login.html" class="hover:text-white">Already have an account? Login</a>7
        </div>

        <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-2 rounded-lg">Create Account</button>
      </form>
    </div>
  </div>
</body>
</html>
