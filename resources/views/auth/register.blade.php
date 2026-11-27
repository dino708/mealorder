@extends('layouts.meal-auth')
@section('title', 'Buat akun baru')
@section('content')
<h1>Buat akun baru</h1><p class="mo-muted">Daftar untuk memesan dan memantau makananmu.</p>
 <form method="POST" action="{{ route('register') }}">@csrf
 <label class="mo-field">Nama lengkap<input name="name" type="text" value="{{ old('name') }}" autocomplete="name" required></label>
@error('name')<p class="mo-auth-error">{{ $message }}</p>@enderror
<label class="mo-field">Email<input name="email" type="email" value="{{ old('email') }}" autocomplete="email" required></label>
@error('email')<p class="mo-auth-error">{{ $message }}</p>@enderror
<label class="mo-field">Kata sandi<input name="password" type="password" autocomplete="new-password" required></label>
@error('password')<p class="mo-auth-error">{{ $message }}</p>@enderror
<label class="mo-field">Ulangi kata sandi<input name="password_confirmation" type="password" autocomplete="new-password" required></label>
@error('password_confirmation')<p class="mo-auth-error">{{ $message }}</p>@enderror

 <button class="mo-btn mo-full" type="submit">Daftar</button>
 </form><p class="mo-auth-foot">Sudah punya akun? <a class="mo-link" href="{{ route('login') }}">Masuk</a></p>
@endsection
