@extends('layouts.meal-auth')
@section('title', 'Verifikasi email')
@section('content')
<h1>Verifikasi email</h1><p class="mo-muted">Periksa kotak masuk untuk tautan verifikasi akun. Kirim ulang bila diperlukan.</p>
<form method="POST" action="{{ route('verification.send') }}">@csrf<button class="mo-btn mo-full">Kirim ulang tautan</button></form>
<form method="POST" action="{{ route('logout') }}">@csrf<button class="mo-btn mo-outline mo-full">Keluar</button></form>
@endsection
