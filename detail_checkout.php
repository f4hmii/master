<html lang="en">
 <head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1" name="viewport"/>
  <title>
   Order Page
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
   <!-- Back arrow -->
   <div class="mb-6">
    <button aria-label="Back" class="text-gray-700 hover:text-gray-900">
     <i class="fas fa-arrow-left">
     </i>
    </button>
   </div>
   <!-- Address Section -->
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
        nama pemesan
       </span>
       <br/>
       <span>
        no telpon
       </span>
      </p>
     </div>
     <div class="flex-1 min-w-[280px] text-xs leading-tight text-gray-800">
      alamat lengkap jalan, RT/RW, Kelurahan, Kecamatan, Kota, Provinsi, Kode Pos
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
   <!-- Produk Dipesan Section -->
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
    <!-- Product item -->
    <div class="mb-3">
     <div class="flex items-center space-x-2 mb-1">
      <span class="text-xs font-semibold text-gray-700 select-text">
       nama seller
      </span>
     </div>
     <div class="flex flex-wrap items-center">
      <img alt="Product image showing brown corduroy pants" class="w-10 h-10 object-cover rounded-sm mr-3" height="40" src="https://storage.googleapis.com/a1aa/image/059c8207-cc8d-4281-346a-a61ad2d2ec6a.jpg" width="40"/>
      <p class="text-xs text-gray-900 font-normal truncate max-w-[180px]">
       nama produk
      </p>
      <span class="ml-2 text-xs text-gray-400 whitespace-nowrap select-none">
       Size: S
      </span>
      <div class="hidden sm:flex sm:flex-1">
      </div>
      <div class="hidden sm:flex sm:space-x-10 sm:items-center">
       <span class="w-24 text-right text-xs font-normal text-gray-900">
        Rp285.000
       </span>
       <span class="w-10 text-center text-xs font-normal text-gray-900">
        1
       </span>
       <span class="w-28 text-right text-xs font-semibold text-gray-900 font-bold">
        Rp285.000
       </span>
      </div>
     </div>
    </div>
    <!-- Protection item -->
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
        Rp10.000
       </span>
    </div>
    <div class="flex justify-end text-xs text-gray-700 font-normal mb-4 select-none">
     <span>
      Total Pesanan (1 Produk):
     </span>
     <span class="ml-2 font-bold text-[#FF5722] text-sm">
      Rp285.000
     </span>
    </div>
   </section>
   <!-- Summary Section -->
   <section class="border-r border-gray-200 pr-6">
    <div class="max-w-md ml-auto text-xs text-gray-700 font-normal space-y-2 select-none">
     <div class="flex justify-between">
      <span>
       Subtotal Pesanan
      </span>
      <span>
       Rp285.000
      </span>
     </div>
     <div class="flex justify-between">
      <span>
       Subtotal Pengiriman
      </span>
      <span>
       Rp10.000
      </span>
     </div>
     <div class="flex justify-between items-center">
     <!-- <span>
        Biaya Layanan
      </span>
      <div class="flex items-center space-x-1">
       <span>
        Rp2.000
       </span>
       <i class="fas fa-question-circle text-gray-400">
       </i>
      </div>
     </div>
     <div class="flex justify-between text-[#FF5722]">
      <span>
       Voucher Diskon
      </span>
      <span>
       -Rp10.000
      </span>-->
     </div>
     <div class="flex justify-between font-bold text-[#FF5722] text-lg">
      <span>
       Total Pembayaran
      </span>
      <span>
       Rp287.000
      </span>
     </div>
    </div>
   </section>
   <!-- Button -->
   <div class="max-w-md ml-auto mt-6">
     <form action="../checkout.php" method="post" class="w-full max-w-xs">
    <button type="submit" class="w-full bg-gray-900 text-white font-semibold text-sm px-6 py-3 rounded hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-600 transition">
      Checkout Sekarang
    </button>
  </form>
   </div>
  </div>
 </body>
</html>
