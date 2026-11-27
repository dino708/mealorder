<?php
use Illuminate\Support\Facades\Artisan;
Artisan::command('meal:about', function () { $this->info('Meal Order - ZeroBug'); })->purpose('Informasi aplikasi');

Artisan::command('meal:admin', function () {
    $name=$this->ask('Nama administrator');
    $email=$this->ask('Email administrator');
    $password=$this->secret('Password (minimal 12 karakter)');
    $data=\Illuminate\Support\Facades\Validator::make(['name'=>$name,'email'=>$email,'password'=>$password],[
        'name'=>'required|string|max:100','email'=>'required|email|max:150|unique:users,email',
        'password'=>'required|string|min:12|max:72'])->validate();
    $user=\App\Models\User::create($data);
    $user->forceFill(['role'=>'admin'])->save();
    $this->info('Administrator berhasil dibuat.');
})->purpose('Buat administrator secara lokal tanpa kredensial bawaan');
