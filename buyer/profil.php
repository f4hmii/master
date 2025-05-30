

<html lang="en">
 <head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1" name="viewport"/>
  <title>
   Profile Page
  </title>
  <script src="https://cdn.tailwindcss.com">
  </script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
 </head>
 <body class="bg-white text-gray-900 font-sans">
  <?php
session_start();
include "../view/header.php";
?>
 
  <!-- Main Content -->
  <main class="flex bg-white">
   <!-- Sidebar -->
   <aside class="bg-gray-100 w-64 p-6 space-y-8">
    <div class="flex items-center space-x-3 border-b border-gray-300 pb-4">
     <i class="fas fa-user-circle text-3xl text-gray-600">
     </i>
     <div>
      <p class="font-semibold text-gray-700 text-sm">
       Username
      </p>
      <p class="text-xs text-gray-400">
       Lorem ipsum
      </p>
     </div>
    </div>
    <nav class="space-y-6 text-xs text-gray-500">
     <div>
      <p class="font-semibold text-gray-700 mb-2">
       Akun Saya
      </p>
      <ul class="space-y-1">
       <li class="hover:text-gray-700 cursor-pointer">
        Profile
       </li>
       <li class="hover:text-gray-700 cursor-pointer">
        Alamat
       </li>
       <li class="hover:text-gray-700 cursor-pointer">
        Ubah Password
       </li>
      </ul>
     </div>
     <p class="font-semibold text-gray-700 cursor-pointer">
      Pesanan Saya
     </p>
     <p class="font-semibold text-gray-700 cursor-pointer">
      Notifikasi
     </p>
     <p class="font-semibold text-gray-700 cursor-pointer">
      Voucher
     </p>
    </nav>
   </aside>
   <!-- Content Area -->
   <section class="flex-1 p-10">
    <h1 class="font-extrabold text-2xl text-gray-700 mb-1">
     Lorem ipsum
    </h1>
    <p class="text-xs text-gray-400 mb-8">
     Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt
    </p>
    <div class="flex space-x-10">
     <!-- Left form -->
     <form class="flex flex-col space-y-6 w-2/3">
      <div class="grid grid-cols-3 items-center border-b border-gray-300 pb-6">
       <label class="text-xs text-gray-700">
        Username
       </label>
       <p class="col-span-2 font-semibold text-gray-700">
        Username
       </p>
      </div>
      <div class="grid grid-cols-3 items-center border-b border-gray-300 pb-6">
       <label class="text-xs text-gray-700">
        Nama
       </label>
       <input class="col-span-2 border border-gray-300 text-xs font-semibold px-2 py-1 focus:outline-none focus:ring-1 focus:ring-gray-600" placeholder="Name" type="text"/>
      </div>
      <div class="grid grid-cols-3 items-center border-b border-gray-300 pb-6">
       <label class="text-xs text-gray-700">
        Email
       </label>
       <p class="col-span-2 font-semibold text-gray-700">
        @gmail.com
       </p>
      </div>
      <div class="grid grid-cols-3 items-center border-b border-gray-300 pb-6">
       <label class="text-xs text-gray-700">
        Role
       </label>
       <p class="col-span-2 font-semibold text-gray-700">
        Buyer
       </p>
      </div>
      <button class="w-40 bg-gray-900 text-white text-xs py-2 rounded-md tracking-widest hover:bg-gray-800" type="submit">
       BTN
      </button>
     </form>
     <!-- Right image and button -->
     <div class="flex flex-col items-center justify-center border-l border-gray-300 pl-10 w-1/3 space-y-6">
      <div class="rounded-full border border-gray-400 p-4">
       <img alt="Placeholder image of a square with mountain and sun icon inside a circular border" class="w-24 h-24 object-cover" height="100" src="https://storage.googleapis.com/a1aa/image/2cb11cd7-b560-4ba4-cd56-c634ffaad324.jpg" width="100"/>
      </div>
      <button class="w-40 border border-gray-400 text-xs py-2 rounded-md tracking-widest hover:bg-gray-100" type="button">
       BTN
      </button>
     </div>
    </div>
   </section>
  </main>
  <!-- Footer -->
  <footer>
   <div class="bg-gray-500 text-white px-10 py-10">
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row md:justify-between md:space-x-20">
     <div class="mb-8 md:mb-0">
      <h2 class="font-bold text-lg mb-3">
       Logo
      </h2>
      <p class="text-xs max-w-xs">
       Lorem ipsum sit dolor amet
      </p>
     </div>
     <div class="grid grid-cols-1 sm:grid-cols-3 gap-10 text-xs">
      <div>
       <h3 class="font-bold mb-3">
        Lorem ipsum
       </h3>
       <ul class="space-y-1">
        <li>
         Lorem ipsum
        </li>
        <li>
         Lorem ipsum
        </li>
        <li>
         Lorem ipsum
        </li>
        <li>
         Lorem ipsum
        </li>
       </ul>
      </div>
      <div>
       <h3 class="font-bold mb-3">
        Lorem ipsum
       </h3>
       <ul class="space-y-1">
        <li>
         Lorem ipsum
        </li>
        <li>
         Lorem ipsum
        </li>
        <li>
         Lorem ipsum
        </li>
        <li>
         Lorem ipsum
        </li>
       </ul>
      </div>
      <div>
       <h3 class="font-bold mb-3">
        Lorem ipsum
       </h3>
       <ul class="space-y-1">
        <li>
         Lorem ipsum
        </li>
        <li>
         Lorem ipsum
        </li>
        <li>
         Lorem ipsum
        </li>
        <li>
         Lorem ipsum
        </li>
       </ul>
      </div>
     </div>
    </div>
   </div>
   <div class="bg-gray-700 text-gray-200 px-10 py-4 flex justify-between items-center">
    <p class="text-xs">
     © 2025 - Lorem ipsum
    </p>
    <div class="flex space-x-6">
     <div class="w-10 h-10 rounded-full bg-gray-300">
     </div>
     <div class="w-10 h-10 rounded-full bg-gray-300">
     </div>
     <div class="w-10 h-10 rounded-full bg-gray-300">
     </div>
    </div>
   </div>
  </footer>
 </body>
</html>
