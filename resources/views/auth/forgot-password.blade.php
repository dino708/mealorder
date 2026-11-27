@extends('layouts.meal-auth')
@section('title', 'Lupa kata sandi')
@section('content')
<h1>Lupa kata sandi?</h1><p class="mo-muted">Masukkan email akun. Tautan reset akan dikirim melalui email jika layanan email sudah dikonfigurasi.</p>
<form method="POST" action="{{ route('password.email') }}">@csrf
<label class="mo-field">Email<input name="email" type="email" value="{{ old('email') }}" required autocomplete="email"></label>
@error('email')<p class="mo-auth-error">{{ $message }}</p>@enderror
<button type="submit" class="mo-btn mo-full">Kirim tautan reset</button></form>
@endsection
