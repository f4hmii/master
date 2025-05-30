<?php
session_start();
include '../db_connection.php'; // Pastikan path ini benar ke file koneksi database Anda
include "../view/header.php";
// Pastikan user sudah login
if (!isset($_SESSION['id'])) {
    header("Location: pages/login.php"); // Sesuaikan dengan path ke halaman login Anda
    exit;
}

$pengguna_id = intval($_SESSION['id']);
$username = $_SESSION['username'] ?? 'Guest';
$role = $_SESSION['role'] ?? 'N/A';

// Ambil data pengguna dari database
$user_data = null;
$stmt_user = $conn->prepare("SELECT nama_pengguna, nomor_telepon, alamat FROM pengguna WHERE pengguna_id = ?");
$stmt_user->bind_param("i", $pengguna_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
if ($result_user->num_rows > 0) {
    $user_data = $result_user->fetch_assoc();
}
$stmt_user->close();

// --- Statistik Dashboard ---

// 1. "Perlu Dikirim" (asumsi untuk pesanan yang dibuat oleh pembeli)
$orders_pending_shipment = 0;
if ($role === 'buyer') {
    $stmt_pending_orders = $conn->prepare("SELECT COUNT(*) AS total FROM pesanan WHERE buyer_id = ? AND (status = 'tertunda_pembayaran' OR status = 'diproses_penjual')");
    $stmt_pending_orders->bind_param("i", $pengguna_id);
    $stmt_pending_orders->execute();
    $result_pending_orders = $stmt_pending_orders->get_result();
    $orders_pending_shipment = $result_pending_orders->fetch_assoc()['total'];
    $stmt_pending_orders->close();
} else if ($role === 'seller') {
    // Untuk penjual, 'perlu dikirim' berarti pesanan yang produknya harus dikirim
    $stmt_seller_pending_orders = $conn->prepare("
        SELECT COUNT(DISTINCT pd.pesanan_id) AS total
        FROM pesanandetail pd
        JOIN produk p ON pd.produk_id = p.produk_id
        JOIN pesanan ps ON pd.pesanan_id = ps.pesanan_id
        WHERE p.seller_id = ? AND (ps.status = 'dibayar' OR ps.status = 'diproses_penjual')
    ");
    $stmt_seller_pending_orders->bind_param("i", $pengguna_id);
    $stmt_seller_pending_orders->execute();
    $result_seller_pending_orders = $stmt_seller_pending_orders->get_result();
    $orders_pending_shipment = $result_seller_pending_orders->fetch_assoc()['total'];
    $stmt_seller_pending_orders->close();
}


// 2. "Return" (placeholder, karena tidak ada tabel khusus 'return' yang terlihat)
$total_returns = 0; // Anda bisa menambah logika di sini jika ada tabel pengembalian


// 3. "Ulasan perlu dibalas" (placeholder, karena tidak ada kolom 'balas' atau 'status' ulasan)
$reviews_to_reply = 0; // Anda bisa menambah logika di sini jika ada sistem balas ulasan

// 4. Total Produk (khusus untuk seller)
$total_products_by_seller = 0;
if ($role === 'seller') {
    $stmt_seller_products = $conn->prepare("SELECT COUNT(*) AS total FROM produk WHERE seller_id = ?");
    $stmt_seller_products->bind_param("i", $pengguna_id);
    $stmt_seller_products->execute();
    $result_seller_products = $stmt_seller_products->get_result();
    $total_products_by_seller = $result_seller_products->fetch_assoc()['total'];
    $stmt_seller_products->close();
}

// 5. Total Pesanan Dibuat (khusus untuk buyer)
$total_orders_by_buyer = 0;
if ($role === 'buyer') {
    $stmt_buyer_orders = $conn->prepare("SELECT COUNT(*) AS total FROM pesanan WHERE buyer_id = ?");
    $stmt_buyer_orders->bind_param("i", $pengguna_id);
    $stmt_buyer_orders->execute();
    $result_buyer_orders = $stmt_buyer_orders->get_result();
    $total_orders_by_buyer = $result_buyer_orders->fetch_assoc()['total'];
    $stmt_buyer_orders->close();
}

// 6. Wallet (placeholder)
$wallet_balance = "Rp0"; // Anda bisa menambah logika jika ada sistem wallet

?>
<html lang="en">
 <head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1" name="viewport"/>
  <title>
   Dashboard
  </title>
  <script src="https://cdn.tailwindcss.com">
  </script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
 </head>
 <body class="bg-white text-gray-900 font-sans">
  <main class="flex flex-col lg:flex-row max-w-7xl mx-auto mt-6 px-6 gap-6">
   <aside class="bg-gray-100 w-full lg:w-64 p-6 flex flex-col space-y-6">
    <div class="flex items-center space-x-3">
     <i class="fas fa-user-circle text-3xl text-gray-600">
     </i>
     <div>
      <p class="font-semibold text-gray-700 text-sm">
       <?= htmlspecialchars($username) ?>
      </p>
      <p class="text-xs text-gray-400">
       <?= htmlspecialchars($role) ?>
      </p>
     </div>
    </div>
    <hr class="border-gray-300"/>
    <div class="flex flex-col space-y-1 text-gray-500 text-sm">
     <p class="font-semibold text-gray-700">
      Akun Saya
     </p>
     <a class="hover:text-gray-700" href="#">
      Profile
     </a>
     <a class="hover:text-gray-700" href="#">
      Alamat
     </a>
     <a class="hover:text-gray-700" href="#">
      Ubah Password
     </a>
     <p class="font-semibold text-gray-700 mt-3">
      Notifikasi
     </p>
    </div>
   </aside>
   <section class="flex-1 flex flex-col space-y-6">
    <div class="bg-gray-600 rounded-md p-6 flex justify-between text-center text-gray-900">
      <div class="flex flex-col items-center space-y-2">
       <div class="bg-gray-300 rounded-md w-16 h-16 flex items-center justify-center text-3xl font-semibold">
        <?= $orders_pending_shipment ?>
       </div>
       <p class="text-xs text-gray-800">
        <?= ($role === 'buyer') ? 'Pesanan perlu dikirim' : 'Pesanan perlu dikirim (Anda)' ?>
       </p>
      </div>
      <div class="flex flex-col items-center space-y-2">
       <div class="bg-gray-300 rounded-md w-16 h-16 flex items-center justify-center text-3xl font-semibold">
        <?= $total_returns ?>
       </div>
       <p class="text-xs text-gray-800">
        Return
       </p>
      </div>
      <div class="flex flex-col items-center space-y-2">
       <div class="bg-gray-300 rounded-md w-16 h-16 flex items-center justify-center text-3xl font-semibold">
        <?= $reviews_to_reply ?>
       </div>
       <p class="text-xs text-gray-800">
        Ulasan perlu dibalas
       </p>
      </div>
    </div>
    <hr class="border-gray-300"/>
    <div class="bg-gray-600 rounded-md p-6 flex justify-between text-center text-gray-300">
     <div class="flex flex-col items-center space-y-2">
      <i class="fas fa-box-open text-3xl">
      </i>
      <p class="font-semibold text-gray-300">
       <?php if ($role === 'seller'): ?>
           <a href="produk.php" class="text-gray-300 hover:text-white">
               Produk Saya (<?= $total_products_by_seller ?>)
           </a>
       <?php else: ?>
           Produk
       <?php endif; ?>
      </p>
     </div>
     <div class="flex flex-col items-center space-y-2">
      <i class="fas fa-wallet text-3xl">
      </i>
      <p class="font-semibold text-gray-300">
       Wallet (<?= $wallet_balance ?>)
      </p>
     </div>
     <div class="flex flex-col items-center space-y-2">
      <i class="fas fa-chart-bar text-3xl">
      </i>
      <p class="font-semibold text-gray-300">
       <?php if ($role === 'buyer'): ?>
           Pesanan Dibuat (<?= $total_orders_by_buyer ?>)
       <?php else: ?>
           Performa
       <?php endif; ?>
      </p>
     </div>
    </div>
    <div class="bg-gray-300 rounded-md p-10 flex justify-center items-center">
     <img alt="Placeholder image of a square with mountain and sun icon inside a circular border" class="rounded-full border border-gray-400" height="80" src="https://storage.googleapis.com/a1aa/image/2f0666ae-ded8-4354-6d2e-e9ef2e02e828.jpg" width="80"/>
    </div>
   </section>
  </main>
  <?php
include "../view/footer.php";
?>
 </body>
</html>