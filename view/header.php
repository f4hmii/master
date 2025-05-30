<?php
$role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <!-- <title>
    Web Page
  </title> -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&amp;display=swap" rel="stylesheet" />
  <script src="https://unpkg.com/feather-icons"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"   />
  <link rel="stylesheet" href="/Tubes_Webpro_MOVR/view/header.css">


<body>
  <div class="navbar">
    <div class="logo">
      <h1>MOVR</h1>
    </div>

    <ul>
      <li><a href="../index.php">Home</a></li>
      <li><a href="aboutfairuz.html">About</a></li>
      <li><a href="../index.php">Produk</a></li>
      <li><a href="announcement.html">Announcement</a></li>
      <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'seller'): ?>
        <li><a href="/TA_webpro/seller/produk.php">Service</a></li>
      <?php endif; ?>
      <li><a href="pages/sale.php">Sale</a></li>
      <li><a href="servicefairuz.html">Service</a></li>

      <!-- Category Toggle Dropdown -->
      <?php $kategori = [
        'Baju' => 'view/kategori.php?kategori=baju',
        'Celana' => 'view/kategori.php?kategori=celana',
        'Sepatu' => 'view/kategori.php?kategori=sepatu',
        'Aksesoris' => 'view/kategori.php?kategori=aksesoris'
      ];
      ?>

      <li class="category-dropdown">
        <div class="category-dropdown-toggle" onclick="toggleCategoryDropdown()">
          <a href="#">Category</a>
        </div>
        <div class="category-dropdown-menu" id="categoryDropdown">
          <?php foreach ($kategori as $nama => $link): ?>
            <a href="<?php echo $link; ?>"><?php echo htmlspecialchars($nama); ?></a>
          <?php endforeach; ?>
        </div>
      </li>

    </ul>

    <form method="GET" action="search.php" class="search-form">
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" name="query" placeholder="Cari produk" required>
      </div>
    </form>

    <div class="icon-wrapper">
      <a href="wishlist/favorite.php" title="Favorit" style="margin-right: 10px;">
        <i data-feather="heart"></i>
      </a>


      <a href="pages/chet.php" title="Chet" style="margin-right: 10px;">
        <i data-feather="message-circle"></i>
      </a>


      <a href="pages/cart.php" title="Keranjang" style="margin-right: 10px;">
        <i data-feather="shopping-cart"></i>
      </a>

      <?php if (isset($_SESSION['username'])): ?>
        <div class="user-dropdown">
          <div class="user-dropdown-toggle" onclick="toggleUserDropdown()">
            <i data-feather="user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
          </div>
          <div class="user-dropdown-menu" id="userDropdown">
            <a href="../Tubes_Webpro_MOVR/buyer/profil.php">Informasi Akun</a>
            <?php if ($role == 'seller'): ?>
              <a href="seller/produk.php">Kontrol Produk</a>
            <?php endif; ?>
            <a href="#" onclick="confirmLogout()">Logout</a>
          </div>
        </div>
      <?php else: ?>
        <a href="pages/login.php">
          <i data-feather="log-in"></i>
        </a>
      <?php endif; ?>
    </div>



  </div>

  <script>
    feather.replace(); // Aktifkan semua ikon feather
  </script>
  <script>
    function toggleUserDropdown() {
      const dropdown = document.getElementById("userDropdown");
      dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
    }

    // Optional: Klik di luar menutup dropdown
    window.addEventListener("click", function(e) {
      const toggle = document.querySelector(".user-dropdown-toggle");
      const menu = document.getElementById("userDropdown");

      if (!toggle.contains(e.target) && !menu.contains(e.target)) {
        menu.style.display = "none";
      }
    });
  </script>
  <script>
    function toggleCategoryDropdown() {
      const dropdown = document.getElementById("categoryDropdown");
      dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
    }

    window.addEventListener("click", function(e) {
      const toggle = document.querySelector(".category-dropdown-toggle");
      const menu = document.getElementById("categoryDropdown");

      if (!toggle.contains(e.target) && !menu.contains(e.target)) {
        menu.style.display = "none";
      }
    });
  </script>
  <script>
    function confirmLogout() {
      const yakin = confirm("Apakah Anda yakin ingin logout?");
      if (yakin) {
        window.location.href = "pages/logout.php"; // Redirect ke logout.php kalau user klik "OK"
      }
    }
  </script>

</body>

</html>