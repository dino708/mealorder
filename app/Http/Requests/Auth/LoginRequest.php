<?php
namespace App\Http\Requests\Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\{Auth,RateLimiter};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
class LoginRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array { return ['email'=>'required|string|email','password'=>'required|string']; }
    public function authenticate(): void {
        $key=Str::lower($this->string('email')).'|'.$this->ip();
        if(RateLimiter::tooManyAttempts($key,5)) throw ValidationException::withMessages(['email'=>'Terlalu banyak percobaan. Coba lagi dalam '.RateLimiter::availableIn($key).' detik.']);
        if(!Auth::attempt($this->only('email','password'),$this->boolean('remember'))){
            RateLimiter::hit($key);throw ValidationException::withMessages(['email'=>'Email atau kata sandi tidak sesuai.']);
        }
        RateLimiter::clear($key);
    }
}
