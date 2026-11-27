<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'Akun') · Meal Order</title>
@vite(['resources/css/app.css','resources/js/app.js'])
</head><body class="mo-auth">
<main class="mo-auth-layout">
    <aside class="mo-auth-art" aria-hidden="true">
        <p class="mo-eyebrow">MEAL ORDER · ZEROBUG</p>
        <h2>Pesan dulu,<br>nikmati nanti.</h2>
        <p>Pilih makanan, pesan sesuai jadwal, dan pilih ambil sendiri atau pengiriman.</p>
        <img src="{{ asset('images/meal-hero.svg') }}" alt="">
    </aside>
    <div class="mo-auth-card">
        <a class="mo-brand" href="{{ route('catalog') }}">Meal<span>Order.</span><small>PRE-ORDER KULINER</small></a>
        <p><a class="mo-link" href="{{ route('catalog') }}">← Kembali ke menu</a></p>
        @if(session('status'))<p class="mo-message" role="status">{{ session('status') }}</p>@endif
        @yield('content')
    </div>
</main></body></html>
