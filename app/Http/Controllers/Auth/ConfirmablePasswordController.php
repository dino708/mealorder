<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
class ConfirmablePasswordController extends Controller {
    public function show(){return view('auth.confirm-password');}
    public function store(Request $request){
        $request->validate(['password'=>'required|string']);
        if(!Hash::check($request->password,$request->user()->password))throw ValidationException::withMessages(['password'=>'Kata sandi tidak sesuai.']);
        $request->session()->put('auth.password_confirmed_at',time());
        return redirect()->intended(route('dashboard',absolute:false));
    }
}
