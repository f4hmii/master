<?php
session_start();
include '../db_connection.php';

// Pastikan user login dan role seller
if (!isset($_SESSION['id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header('Location: ../login.php');
    exit;
}
$seller_id = intval($_SESSION['id']);

$sql = "SELECT * FROM produk WHERE seller_id = $seller_id ORDER BY produk_id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <title>Data Produk Saya</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">

    <a href="../index.php" class="inline-block mb-6 px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition">← Kembali</a>
    <h1 class="text-3xl font-bold mb-6">Data Produk Saya</h1>
    <a href="tambah.php" class="inline-block mb-8 px-6 py-3 bg-blue-600 text-white font-semibold rounded hover:bg-blue-700 transition">Tambah Produk</a>

    <?php if ($result->num_rows === 0): ?>
        <p class="text-gray-600">Belum ada produk yang Anda upload.</p>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php while ($row = $result->fetch_assoc()):
                // Decode size_stock dan color_stock JSON
                $size_stock = json_decode($row['size_stock'], true) ?: [];
                $color_stock = json_decode($row['color_stock'], true) ?: [];
            ?>
                <div class="bg-white rounded-lg shadow p-4 flex flex-col">
                    <img src="../uploads/<?= htmlspecialchars($row['foto_url']) ?>" alt="<?= htmlspecialchars($row['nama_produk']) ?>" class="w-full h-48 object-contain rounded mb-4" />

                    <h2 class="font-semibold text-lg mb-2 truncate"><?= htmlspecialchars($row['nama_produk']) ?></h2>

                    <p class="text-sm text-gray-700 mb-2 line-clamp-3"><?= htmlspecialchars($row['deskripsi']) ?></p>

                    <div class="mb-2">
                        <strong>Harga:</strong> Rp <?= number_format($row['harga'], 0, ',', '.') ?>
                    </div>

                    <div class="mb-2">
                        <strong>Stok Keseluruhan:</strong> <?= intval($row['stock']) ?>
                    </div>

                    <div class="mb-2">
                        <strong>Ukuran & Stok:</strong>
                        <?php if (count($size_stock) === 0): ?>
                            <span class="text-gray-500">-</span>
                        <?php else: ?>
                            <ul class="list-disc list-inside text-sm">
                                <?php foreach ($size_stock as $size => $stok): ?>
                                    <li><?= htmlspecialchars($size) ?>: <?= intval($stok) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <strong>Warna & Stok:</strong>
                        <?php if (count($color_stock) === 0): ?>
                            <span class="text-gray-500">-</span>
                        <?php else: ?>
                            <ul class="list-disc list-inside text-sm">
                                <?php foreach ($color_stock as $color => $stok): ?>
                                    <li><?= htmlspecialchars($color) ?>: <?= intval($stok) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <div class="mt-auto flex justify-between">
                        <a href="edit.php?produk_id=<?= $row['produk_id'] ?>" class="px-3 py-1 bg-yellow-400 rounded hover:bg-yellow-500 text-black text-sm">Edit</a>
                        <a href="hapus.php?produk_id=<?= $row['produk_id'] ?>" onclick="return confirm('Yakin mau hapus produk ini?')" class="px-3 py-1 bg-red-600 rounded hover:bg-red-700 text-white text-sm">Hapus</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>

</body>
</html>
