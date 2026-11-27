<header class="mo-header"><div class="mo-header-inner">
    <a href="#menu" class="mo-brand"><span class="mo-mark" aria-hidden="true">M</span><span class="mo-wordmark">meal<span>order</span><small>PRE-ORDER KULINER</small></span></a>
    <nav aria-label="Navigasi pelanggan">
        <a data-nav="menu" href="#menu"><span aria-hidden="true">▦</span> Menu</a>
        <a data-nav="keranjang" href="#keranjang"><span aria-hidden="true">▣</span> Keranjang <span id="cart-count" class="mo-count">0</span></a>
        @auth <a data-nav="pesanan" href="#pesanan"><span aria-hidden="true">☷</span> Pesanan saya</a> @endauth
    </nav>
    <div class="mo-account">@auth
        <a class="mo-link mo-account-name" href="{{ route('profile.edit') }}">{{ auth()->user()->name }}</a>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="mo-btn mo-outline">Keluar</button></form>
        @else <a class="mo-link" href="{{ route('login') }}">Masuk</a><a class="mo-btn" href="{{ route('register') }}">Daftar</a> @endauth
    </div>
</div></header>
<div class="mo-container">
    <p id="message" class="mo-message" role="status" aria-live="polite" hidden></p>
    <section data-page="menu">
        <div class="mo-hero">
            <div class="mo-hero-copy">
                <p class="mo-eyebrow"><span class="mo-dot"></span> PRE-ORDER • PESAN LEBIH AWAL</p>
                <h1>Pesan lebih awal,<br><em>nikmati tepat waktu.</em></h1>
                <p>Pilih menu favorit, tentukan jadwal, lalu ambil pesananmu saat siap.</p>
                <a class="mo-btn" href="#menu-list" id="browse-menu">Lihat pilihan menu <span aria-hidden="true">↓</span></a>
            </div>
            <div class="mo-hero-art" aria-hidden="true"><img src="{{ asset('images/meal-hero.svg') }}" alt="" width="480" height="360"></div>
            <span class="mo-hero-tag">01 / PESAN DULU, NIKMATI NANTI</span>
        </div>
        <div class="mo-how" aria-label="Cara memesan"><span><b>01</b><span><strong>Pilih menu</strong><small>Masukkan ke keranjang</small></span></span><span><b>02</b><span><strong>Buat pesanan</strong><small>Pilih jadwal pre-order</small></span></span><span><b>03</b><span><strong>Terima makanan</strong><small>Ambil saat siap</small></span></span></div>
        <div class="mo-shop-grid">
            <div class="mo-shop-main">
                <div id="menu-list" class="mo-section-head mo-menu-title">
                    <div><p class="mo-eyebrow">KATALOG</p><h2>Menu pilihan hari ini</h2><p id="schedule-hint">Memuat jadwal pre-order...</p></div>
                    <label class="mo-search"><span aria-hidden="true">⌕</span><input id="search" type="search" placeholder="Cari menu..." aria-label="Cari makanan"></label>
                </div>
                <div id="categories" class="mo-chips"></div>
                <p id="catalog-count" class="mo-results" aria-live="polite"></p>
                <div id="catalog" class="mo-catalog"><p>Memuat menu...</p></div>
            </div>
            <aside class="mo-basket-preview" aria-label="Ringkasan keranjang">
                <div class="mo-basket-title"><span class="mo-basket-icon" aria-hidden="true">▣</span><div><h2>Keranjangmu</h2><p>Pesan untuk pengambilan</p></div></div>
                <div id="basket-preview-items"></div>
                <div class="mo-basket-total"><span>Subtotal menu</span><strong id="basket-preview-total">Rp0</strong></div>
                <a href="#keranjang" class="mo-btn mo-full">Lihat keranjang →</a>
                <p class="mo-muted">Harga dan stok diperiksa lagi sebelum pesanan dibuat.</p>
            </aside>
        </div>
    </section>
    <section data-page="keranjang" hidden>
        <div class="mo-section-head"><div><p class="mo-eyebrow">PESANAN / 01</p><h2>Keranjang kamu</h2><p>Periksa jumlah menu, jadwal, dan cara menerima makanan.</p></div><a href="#menu" class="mo-link">← Kembali ke menu</a></div>
        <div class="mo-checkout-grid">
            <div class="mo-panel"><div class="mo-panel-label">01 / PILIHAN MENU</div><h2>Item pesanan</h2><div id="cart"></div><a href="#menu" class="mo-link mo-cart-more">+ Pilih makanan lainnya</a></div>
            <div class="mo-panel"><div class="mo-panel-label">02 / KONFIRMASI</div><h2>Detail checkout</h2><label class="mo-field">Jadwal pre-order<select id="schedule"></select><small id="schedule-detail" class="mo-schedule-detail"></small></label>@auth <p class="mo-muted">Pelanggan: {{ auth()->user()->name }} · {{ auth()->user()->email }}</p> @endauth
                <label class="mo-field">Cara menerima pesanan<select id="fulfillment"><option value="pickup">Ambil sendiri (pickup)</option><option value="delivery">Diantar (delivery)</option></select></label>
                <div id="delivery-fields" hidden><label class="mo-field">Alamat lengkap pengiriman<textarea id="delivery-address" maxlength="500" placeholder="Jalan, nomor rumah, patokan"></textarea></label><label class="mo-field">Nomor telepon<input id="contact-phone" type="tel" maxlength="20" value="{{ auth()->user()?->phone }}" placeholder="08..."></label></div>
                <label class="mo-field">Metode pembayaran<select id="payment-method"><option value="cash">Tunai</option><option value="transfer">Transfer manual</option></select></label>
                <div id="checkout-totals" class="mo-costs"></div>
                <label class="mo-field">Catatan untuk penjual (opsional)<textarea id="notes" maxlength="1000" placeholder="Misalnya: sambal dipisah"></textarea></label>
                <div class="mo-payment-note"><strong>Pembayaran manual</strong><p>Bayar langsung sesuai arahan penjual. Admin akan mencatat pembayaran.</p></div>
                <button id="checkout" disabled class="mo-btn mo-full">{{ auth()->check() ? 'Buat pesanan' : 'Masuk untuk checkout' }}</button><button id="quote" disabled class="mo-btn mo-outline mo-full">Cek estimasi dari server</button>
            </div>
        </div>
    </section>
    @auth
    <section data-page="pesanan" hidden><div class="mo-section-head"><div><p class="mo-eyebrow">PANTAU PESANAN</p><h2>Pesanan saya</h2><p>Pantau proses memasak hingga pesanan siap diterima.</p></div><button id="refresh-orders" class="mo-btn mo-outline">↻ Segarkan</button></div><div id="orders"><p>Memuat pesanan...</p></div></section>
    @endauth
<dialog id="food-detail" class="mo-dialog"><button type="button" id="detail-close" class="mo-btn mo-outline">Tutup</button><div id="detail-content"></div></dialog>
</div>
<a href="#keranjang" id="cart-bar" class="mo-cart-bar" hidden><span id="cart-summary"></span><strong>Lihat keranjang →</strong></a>
<footer class="mo-footer"><strong>mealorder.</strong> · Pre-order kuliner<br>Pre-order dari satu penjual · Pembayaran manual</footer>
