<aside class="mo-sidebar">
<a class="mo-brand" href="#dashboard"><span class="mo-mark" aria-hidden="true">M</span><span class="mo-wordmark">meal<span>order</span><small>RUANG PENJUAL</small></span></a>
<button class="mo-mobile-toggle" id="admin-toggle" type="button" aria-expanded="false" aria-controls="admin-nav">☰ Menu penjual</button>
<nav id="admin-nav" aria-label="Navigasi admin"><p class="mo-nav-caption">OPERASIONAL</p>
<a data-nav="dashboard" href="#dashboard"><span class="mo-nav-icon" aria-hidden="true">▦</span> Ringkasan</a>
<a data-nav="pesanan" href="#pesanan"><span class="mo-nav-icon" aria-hidden="true">☷</span> Pesanan masuk <span id="pending-count" class="mo-count"></span></a>
<p class="mo-nav-caption">PENGELOLAAN TOKO</p><a data-nav="menu" href="#menu"><span class="mo-nav-icon" aria-hidden="true">♨</span> Kelola menu</a>
<a data-nav="kategori" href="#kategori"><span class="mo-nav-icon" aria-hidden="true">▤</span> Kategori</a>
<a data-nav="jadwal" href="#jadwal"><span class="mo-nav-icon" aria-hidden="true">▣</span> Jadwal PO</a>
<a data-nav="pelanggan" href="#pelanggan"><span class="mo-nav-icon" aria-hidden="true">♙</span> Pelanggan</a>
<a data-nav="laporan" href="#laporan"><span class="mo-nav-icon" aria-hidden="true">↗</span> Laporan</a>
</nav>
<div class="mo-sidebar-foot"><strong>{{ auth()->user()->name }}</strong><small>Administrator</small><a class="mo-side-profile" href="{{ route('profile.edit') }}">Pengaturan akun</a><form method="POST" action="{{ route('logout') }}">@csrf<button class="mo-logout">Keluar akun ↗</button></form></div>
</aside>
<div class="mo-workspace">
<header class="mo-admin-head"><div><small>MEAL ORDER / PANEL PENJUAL</small><h1 id="page-title">Ringkasan</h1></div><button id="refresh-admin" class="mo-btn mo-outline">↻ Segarkan data</button></header>
<p id="message" class="mo-message" role="status" aria-live="polite" hidden></p>
<section data-page="dashboard">
<div class="mo-welcome"><div><p class="mo-eyebrow">TOKO ANDA</p><h2>Halo, {{ auth()->user()->name }}!</h2><p>Cek pesanan baru, kelola menu, dan siapkan makanan tepat waktu.</p></div><a class="mo-btn mo-accent" href="#pesanan">Buka pesanan masuk →</a></div>
<div id="metrics" class="mo-metrics" aria-live="polite"><p>Memuat ringkasan...</p></div>
<div class="mo-panel"><h2>Tren transaksi</h2><div id="dashboard-chart"></div></div><h2 class="mo-subheading">Aksi cepat</h2><div class="mo-quick"><a href="#menu"><span>01</span><strong>Kelola menu makanan</strong><small>Harga, stok dan foto</small></a><a href="#kategori"><span>02</span><strong>Kelola kategori</strong><small>Kelompokkan menu</small></a><a href="#jadwal"><span>03</span><strong>Buka jadwal PO</strong><small>Waktu dan kuota pesanan</small></a></div>
<div class="mo-panel"><div class="mo-section-head"><div><h2>Pesanan terbaru</h2><p>Lihat pesanan terbaru dan lanjutkan prosesnya dari menu Pesanan masuk.</p></div><a href="#pesanan" class="mo-link">Lihat semua →</a></div><div id="recent-orders"></div></div>
</section>
<section data-page="pesanan" hidden><div class="mo-panel"><div class="mo-section-head"><div><h2>Pesanan masuk</h2><p>Pilih pesanan, terima, masak, tandai siap, lalu selesaikan. Pembayaran dicatat manual.</p></div></div><div id="order-filters" class="mo-chips"></div><label class="mo-search mo-order-search"><span>⌕</span><input id="order-search" type="search" placeholder="Cari nama pelanggan / kode pesanan" aria-label="Cari pesanan"></label><label class="mo-field">Tanggal pesanan<input type="date" id="order-date"></label><div id="orders" class="mo-order-grid"><p>Memuat pesanan...</p></div></div></section>
<section data-page="menu" hidden><div id="admin-products"><p>Memuat data...</p></div></section>
<section data-page="kategori" hidden><div id="admin-categories"><p>Memuat data...</p></div></section>
<section data-page="jadwal" hidden><div id="admin-schedules"><p>Memuat data...</p></div></section>
<section data-page="pelanggan" hidden><div class="mo-panel"><h2>Pelanggan</h2><label class="mo-field">Cari pelanggan<input id="customer-search" type="search" placeholder="Nama atau email"></label><div id="customer-list"></div></div></section>
<section data-page="laporan" hidden><div class="mo-panel"><h2>Laporan penjualan</h2><p>Transaksi selesai dan lunas, berdasarkan tanggal penyelesaian.</p><div class="mo-form-grid"><label class="mo-field">Dari tanggal<input id="report-from" type="date"></label><label class="mo-field">Sampai tanggal<input id="report-to" type="date"></label></div><div class="mo-actions"><button class="mo-btn" id="report-apply">Terapkan filter</button><a class="mo-btn mo-outline" id="report-export" href="/api/admin/report/export">Export CSV</a></div><div id="report" class="mo-metrics"></div><div id="report-chart"></div><div id="report-transactions"></div></div></section>
</div>
