<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Pengaturan akun · Meal Order</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body class="mo"><main class="mo-profile"><a class="mo-brand" href="{{ route('dashboard') }}">Meal<span>Order.</span></a>
<p><a class="mo-link" href="{{ route('dashboard') }}">← Kembali ke aplikasi</a></p><h1>Pengaturan akun</h1>
<section class="mo-panel"><h2>Informasi akun</h2>
@if(session('status') === 'profile-updated')<p class="mo-message">Profil berhasil disimpan.</p>@endif
<form method="POST" action="{{ route('profile.update') }}">@csrf @method('PATCH')
<label class="mo-field">Nama<input name="name" value="{{ old('name', $user->name) }}" required></label>
@error('name')<p class="mo-auth-error">{{ $message }}</p>@enderror
<label class="mo-field">Email<input name="email" type="email" value="{{ old('email', $user->email) }}" required></label>
@error('email')<p class="mo-auth-error">{{ $message }}</p>@enderror
<button class="mo-btn mo-full">Simpan profil</button></form></section>
<section class="mo-panel"><h2>Ubah kata sandi</h2>
@if(session('status') === 'password-updated')<p class="mo-message">Kata sandi berhasil diubah.</p>@endif
<form method="POST" action="{{ route('password.update') }}">@csrf @method('PUT')
<label class="mo-field">Kata sandi sekarang<input name="current_password" type="password" autocomplete="current-password" required></label>
@error('current_password', 'updatePassword')<p class="mo-auth-error">{{ $message }}</p>@enderror
<label class="mo-field">Kata sandi baru<input name="password" type="password" autocomplete="new-password" required></label>
@error('password', 'updatePassword')<p class="mo-auth-error">{{ $message }}</p>@enderror
@error('password', 'updatePassword')<p class="mo-auth-error">{{ $message }}</p>@enderror
<label class="mo-field">Ulangi kata sandi baru<input name="password_confirmation" type="password" autocomplete="new-password" required></label>
<button class="mo-btn mo-full">Ubah kata sandi</button></form></section></main></body></html>
