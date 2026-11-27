<?php
namespace App\Http\Controllers;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class ProfileController extends Controller {
    public function edit(Request $request){return view('profile.edit',['user'=>$request->user()]);}
    public function update(ProfileUpdateRequest $request){
        $user=$request->user();$user->fill($request->validated());
        if($user->isDirty('email'))$user->email_verified_at=null;
        $user->save();return back()->with('status','profile-updated');
    }
    public function destroy(Request $request){
        // Akun dan riwayat transaksi tidak dihapus lewat UI atau endpoint ini.
        abort(403,'Penghapusan akun dinonaktifkan untuk menjaga riwayat transaksi.');
    }
}
