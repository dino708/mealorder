@extends('layouts.meal-auth')
@section('title', 'Atur ulang kata sandi')
@section('content')
<h1>Atur ulang kata sandi</h1>
<form method="POST" action="{{ route('password.store') }}">@csrf
<input type="hidden" name="token" value="{{ $request->route('token') }}">
<label class="mo-field">Email<input name="email" type="email" value="{{ old('email', $request->email) }}" required></label>
@error('email')<p class="mo-auth-error">{{ $message }}</p>@enderror
<label class="mo-field">Kata sandi baru<input name="password" type="password" required autocomplete="new-password"></label>
@error('password')<p class="mo-auth-error">{{ $message }}</p>@enderror
@error('password')<p class="mo-auth-error">{{ $message }}</p>@enderror
<label class="mo-field">Ulangi kata sandi<input name="password_confirmation" type="password" required autocomplete="new-password"></label>
<button type="submit" class="mo-btn mo-full">Simpan kata sandi baru</button></form>
@endsection
