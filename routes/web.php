<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
Route::get('/',fn()=>auth()->user()?->role==='admin'?redirect()->route('admin.dashboard'):view('meal'))->name('catalog');
Route::get('/dashboard',fn()=>auth()->user()->role==='admin'?redirect()->route('admin.dashboard'):view('meal'))->middleware('auth')->name('dashboard');
Route::view('/admin','meal')->middleware(['auth','role:admin'])->name('admin.dashboard');
Route::middleware('auth')->group(function(){
    Route::get('/profile',[ProfileController::class,'edit'])->name('profile.edit');
    Route::patch('/profile',[ProfileController::class,'update'])->name('profile.update');
    Route::delete('/profile',[ProfileController::class,'destroy'])->name('profile.destroy');
});
require __DIR__.'/auth.php';
