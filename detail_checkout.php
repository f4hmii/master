<?php
session_start();
include 'db_connection.php'; // Menggunakan db_connection.php

// Pastikan user sudah login
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$pengguna_id = intval($_SESSION['id']);
$pesanan_id = isset($_GET['pesanan_id']) ? intval($_GET['pesanan_id']) : 0;

if ($pesanan_id === 0) {
    // Jika pesanan_id tidak ada, bisa diarahkan ke halaman daftar pesanan atau tampilkan pesan error
    die("ID Pesanan tidak ditemukan. Harap buat pesanan terlebih dahulu dari keranjang.");
}

// Ambil detail pesanan
$stmt_pesanan = $conn->prepare("SELECT * FROM pesanan WHERE pesanan_id = ? AND buyer_id = ?");
$stmt_pesanan->bind_param("ii", $pesanan_id, $pengguna_id);
$stmt_pesanan->execute();
$result_pesanan = $stmt_pesanan->get_result();
$pesanan = $result_pesanan->fetch_assoc();
$stmt_pesanan->close();

if (!$pesanan) {
    die("Pesanan tidak ditemukan atau Anda tidak memiliki akses ke pesanan ini.");
}

// Ambil data pembeli (pengguna)
$stmt_buyer = $conn->prepare("SELECT nama_pengguna, nomor_telepon, alamat FROM pengguna WHERE pengguna_id = ?");
$stmt_buyer->bind_param("i", $pesanan['buyer_id']);
$stmt_buyer->execute();
$result_buyer = $stmt_buyer->get_result();
$buyer_data = $result_buyer->fetch_assoc();
$stmt_buyer->close();

// Ambil detail produk dalam pesanan
$stmt_detail_pesanan = $conn->prepare("
    SELECT
        pd.produk_id,
        pd.quantity,        /* Menggunakan 'quantity' sesuai DB */
        pd.harga_produk,    /* Menggunakan 'harga_produk' sesuai DB */
        pd.color,
        pd.size,
        p.nama_produk,
        p.foto_url,
        s.nama_pengguna AS seller_name
    FROM pesanandetail pd /* Menggunakan 'pesanandetail' sesuai koreksi Anda */
    JOIN produk p ON pd.produk_id = p.produk_id
    JOIN pengguna s ON p.seller_id = s.pengguna_id
    WHERE pd.pesanan_id = ?
");
$stmt_detail_pesanan->bind_param("i", $pesanan_id);
$stmt_detail_pesanan->execute();
$result_detail_pesanan = $stmt_detail_pesanan->get_result();
$stmt_detail_pesanan->close();

$total_produk_harga = 0;
$jumlah_produk = 0;
$items_in_order = [];
while ($item = $result_detail_pesanan->fetch_assoc()) {
    $subtotal_item = $item['quantity'] * $item['harga_produk']; // Pastikan menggunakan 'quantity' dan 'harga_produk' di sini
    $total_produk_harga += $subtotal_item;
    $jumlah_produk += $item['quantity'];
    $items_in_order[] = $item;
}

// Asumsi biaya pengiriman tetap Rp20.000 (sesuai yang dihitung di checkout.php)
$biaya_pengiriman = 20000;
$total_pembayaran = $total_produk_harga + $biaya_pengiriman;
?>

<!DOCTYPE html>
<html lang="en">
 <head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1" name="viewport"/>
  <title>
   Detail Pesanan #<?= htmlspecialchars($pesanan['pesanan_id']) ?>
  </title>
  <script src="https://cdn.tailwindcss.com">
  </script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&amp;display=swap" rel="stylesheet"/>
  <style>
   body {
      font-family: 'Inter', sans-serif;
    }
  </style>
 </head>
 <body class="bg-white text-gray-900">
  <div class="max-w-5xl mx-auto px-4 py-6">
   <div class="mb-6">
    <button aria-label="Back" class="text-gray-700 hover:text-gray-900" onclick="history.back()">
     <i class="fas fa-arrow-left">
     </i>
    </button>
   </div>
   <section class="border-r border-gray-200 pr-6 mb-6">
    <div class="flex items-center space-x-2 mb-2">
     <i class="fas fa-map-marker-alt text-[#FF5722] text-sm">
     </i>
     <h2 class="text-[#FF5722] text-sm font-semibold select-none">
      Alamat Pengiriman
     </h2>
    </div>
    <div class="flex flex-wrap justify-between items-start border-b border-gray-200 pb-3">
     <div class="flex-1 min-w-[220px]">
      <p class="font-semibold text-sm leading-tight">
       <span>
        <?= htmlspecialchars($buyer_data['nama_pengguna']) ?>
       </span>
       <br/>
       <span>
        <?= htmlspecialchars($buyer_data['nomor_telepon']) ?>
       </span>
      </p>
     </div>
     <div class="flex-1 min-w-[280px] text-xs leading-tight text-gray-800">
      <?= nl2br(htmlspecialchars($buyer_data['alamat'])) ?>
     </div>
     <div class="flex items-center space-x-3 whitespace-nowrap">
      <button class="text-sm font-semibold text-[#0047AB] hover:underline">
       Ubah
      </button>
     </div>
    </div>
    
    <div class="h-2 bg-gray-100 mt-4">
    </div>
   </section>
   <section class="border-r border-gray-200 pr-6">
    <div class="flex justify-between items-center mb-4">
     <h3 class="text-sm font-normal text-gray-900 select-none">
      Produk Dipesan
     </h3>
     <div class="hidden sm:flex space-x-20 text-xs text-gray-500 font-normal select-none">
      <span class="w-24 text-right">
       Harga Satuan
      </span>
      <span class="w-10 text-center">
       Jumlah
      </span>
      <span class="w-28 text-right">
       Subtotal Produk
      </span>
     </div>
    </div>
    <?php foreach ($items_in_order as $item):
        $subtotal_item = $item['quantity'] * $item['harga_produk'];
    ?>
    <div class="mb-3">
     <div class="flex items-center space-x-2 mb-1">
      <span class="text-xs font-semibold text-gray-700 select-text">
       <?= htmlspecialchars($item['seller_name']) ?>
      </span>
     </div>
     <div class="flex flex-wrap items-center">
      <img alt="<?= htmlspecialchars($item['nama_produk']) ?>" class="w-10 h-10 object-cover rounded-sm mr-3" height="40" src="uploads/<?= htmlspecialchars($item['foto_url']) ?>" width="40" onerror="this.onerror=null; this.src='uploads/image-not-found.png';"/>
      <p class="text-xs text-gray-900 font-normal truncate max-w-[180px]">
       <?= htmlspecialchars($item['nama_produk']) ?>
      </p>
      <span class="ml-2 text-xs text-gray-400 whitespace-nowrap select-none">
       <?php
       if (!empty($item['size']) && !empty($item['color'])) {
           echo "Size: " . htmlspecialchars($item['size']) . ", Warna: " . htmlspecialchars($item['color']);
       } elseif (!empty($item['size'])) {
           echo "Size: " . htmlspecialchars($item['size']);
       } elseif (!empty($item['color'])) {
           echo "Warna: " . htmlspecialchars($item['color']);
       }
       ?>
      </span>
      <div class="hidden sm:flex sm:flex-1">
      </div>
      <div class="hidden sm:flex sm:space-x-10 sm:items-center">
       <span class="w-24 text-right text-xs font-normal text-gray-900">
        Rp<?= number_format($item['harga_produk'], 0, ',', '.') ?>
       </span>
       <span class="w-10 text-center text-xs font-normal text-gray-900">
        <?= htmlspecialchars($item['quantity']) ?>
       </span>
       <span class="w-28 text-right text-xs font-semibold text-gray-900 font-bold">
        Rp<?= number_format($subtotal_item, 0, ',', '.') ?>
       </span>
      </div>
     </div>
    </div>
    <?php endforeach; ?>
    <div class="border border-gray-200 rounded-md p-3 mb-4">
     <label class="flex items-start space-x-3 cursor-pointer select-none">
      
      <div class="flex-1 text-xs text-gray-900">
       <p class="font-semibold inline-flex items-center space-x-1">
       </p>
    
      </div>
      <div class="hidden sm:flex sm:flex-col sm:items-center sm:space-y-1 sm:ml-6">
      
      </div>
     </label>
    </div>
    <div class="h-[1px] bg-gray-100 mb-4">
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 border border-gray-200 rounded-md overflow-hidden mb-4">
     <label class="flex items-center border-b sm:border-b-0 sm:border-r border-gray-200 px-4 py-3 text-xs text-gray-700 select-none min-w-[120px]" for="pesan">
      Pesan:
     </label>
     <input class="border-none focus:ring-0 focus:outline-none px-4 py-3 text-xs text-gray-500 placeholder-gray-400 w-full" id="pesan" placeholder="(Opsional) Tinggalkan pesan" type="text"/>
     <div class="border-t sm:border-t-0 sm:border-l border-gray-200 px-4 py-3 text-xs text-gray-700">
      <div class="flex justify-between items-center mb-1">
       <span>
        Opsi Pengiriman:
       </span>
       <div class="flex items-center space-x-1 font-semibold text-gray-900">
        <span>
         Reguler
        </span>
       </div>
       <button class="text-xs font-semibold text-[#0047AB] hover:underline ml-3 whitespace-nowrap">
        Ubah
       </button>
       <span class="font-semibold text-xs text-gray-900 ml-auto whitespace-nowrap">
        Rp<?= number_format($biaya_pengiriman, 0, ',', '.') ?>
       </span>
    </div>
    <div class="flex justify-end text-xs text-gray-700 font-normal mb-4 select-none">
     <span>
      Total Pesanan (<?= $jumlah_produk ?> Produk):
     </span>
     <span class="ml-2 font-bold text-[#FF5722] text-sm">
      Rp<?= number_format($total_produk_harga, 0, ',', '.') ?>
     </span>
    </div>
   </section>
   <section class="border-r border-gray-200 pr-6">
    <div class="max-w-md ml-auto text-xs text-gray-700 font-normal space-y-2 select-none">
     <div class="flex justify-between">
      <span>
       Subtotal Pesanan
      </span>
      <span>
       Rp<?= number_format($total_produk_harga, 0, ',', '.') ?>
      </span>
     </div>
     <div class="flex justify-between">
      <span>
       Subtotal Pengiriman
      </span>
      <span>
       Rp<?= number_format($biaya_pengiriman, 0, ',', '.') ?>
      </span>
     </div>
     <div class="flex justify-between font-bold text-[#FF5722] text-lg">
      <span>
       Total Pembayaran
      </span>
      <span>
       Rp<?= number_format($total_pembayaran, 0, ',', '.') ?>
      </span>
     </div>
    </div>
   </section>
   <div class="max-w-md ml-auto mt-6">
     <button type="button" id="payNowButton" class="w-full bg-red-600 text-white font-semibold text-sm px-6 py-3 rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-600 transition">
       Lanjutkan Pembayaran
     </button>
   </div>
  </div>

  <script>
    document.getElementById('payNowButton').addEventListener('click', function() {
        // Arahkan ke checkout.php dengan action 'pay' dan pesanan_id
        window.location.href = 'checkout.php?action=pay&pesanan_id=' + <?= $pesanan_id ?>;
    });
  </script>
 </body>
</html>