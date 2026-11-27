@extends('layouts.meal-auth')
@section('title', 'Konfirmasi kata sandi')
@section('content')
<h1>Konfirmasi kata sandi</h1><p class="mo-muted">Masukkan kata sandi untuk melanjutkan tindakan sensitif.</p>
<form method="POST" action="{{ route('password.confirm') }}">@csrf
<label class="mo-field">Kata sandi<input name="password" type="password" required autocomplete="current-password"></label>
@error('password')<p class="mo-auth-error">{{ $message }}</p>@enderror
<button class="mo-btn mo-full" type="submit">Konfirmasi</button></form>
@endsection
