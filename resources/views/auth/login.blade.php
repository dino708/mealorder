@extends('layouts.meal-auth')
@section('title', 'Masuk ke akun')
@section('content')
<h1>Masuk ke akun</h1><p class="mo-muted">Lanjutkan pesanan makanan favoritmu.</p>
 <form method="POST" action="{{ route('login') }}">@csrf
 <label class="mo-field">Email<input name="email" type="email" value="{{ old('email') }}" autocomplete="email" required></label>
@error('email')<p class="mo-auth-error">{{ $message }}</p>@enderror
<label class="mo-field">Kata sandi<input name="password" type="password" autocomplete="current-password" required></label>
@error('password')<p class="mo-auth-error">{{ $message }}</p>@enderror
<label class="mo-field" style="flex-direction:row;align-items:center"><input type="checkbox" name="remember"> Ingat saya</label>
@if(Route::has('password.request'))<a class="mo-link" href="{{ route('password.request') }}">Lupa kata sandi?</a>@endif
 <button class="mo-btn mo-full" type="submit">Masuk</button>
 </form><p class="mo-auth-foot">Belum punya akun? <a class="mo-link" href="{{ route('register') }}">Daftar</a></p>
@endsection
