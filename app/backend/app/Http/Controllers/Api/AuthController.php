<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller {
 public function register(Request $r){
  $data=$r->validate(['name'=>'required|string|max:160','phone'=>'required|string|max:30|unique:customers,phone','national_id'=>'nullable|string|max:20|unique:customers,national_id','password'=>'required|string|min:4']);
  $c=Customer::create(['name'=>$data['name'],'phone'=>$data['phone'],'national_id'=>$data['national_id'] ?? null,'customer_no'=>'CUS-'.str_pad((string)(Customer::where('role','customer')->count()+1),4,'0',STR_PAD_LEFT),'password_hash'=>Hash::make($data['password']),'role'=>'customer','is_active'=>true]);
  $token=$c->createToken('web')->plainTextToken;
  return response()->json(['customer'=>$c,'token'=>$token],201);
 }
 public function login(Request $r){
  $data=$r->validate(['identifier'=>'required|string','password'=>'required|string']);
  $identifier=trim($data['identifier']);
  $c=Customer::where(function($q) use($identifier){
      $q->where('phone',$identifier)->orWhere('national_id',$identifier)->orWhere('customer_no',$identifier);
    })->where('is_active',true)->first();
  if(!$c||!Hash::check($data['password'],$c->password_hash)) throw ValidationException::withMessages(['phone'=>'اطلاعات ورود صحیح نیست.']);
  $token=$c->createToken('web')->plainTextToken;
  return ['customer'=>$c,'token'=>$token];
 }
 public function adminLogin(Request $r){
  $data=$r->validate(['username'=>'required|string|max:160','password'=>'required|string']);
  $username = trim((string)$data['username']);
  $c=Customer::where(function($q) use ($username){
      $q->where('phone',$username)->orWhere('email',$username);
    })->whereIn('role',['seller','manager','super_admin'])->where('is_active',true)->first();
  if(!$c||!Hash::check($data['password'],$c->password_hash)) throw ValidationException::withMessages(['username'=>'نام کاربری یا رمز عبور صحیح نیست.']);
  $token=$c->createToken('admin-web',['admin'])->plainTextToken;
  return ['customer'=>$c,'token'=>$token];
 }
 public function logout(Request $r){$r->user()->currentAccessToken()?->delete();return ['ok'=>true];}
 public function me(Request $r){return ['customer'=>$r->user()];}
}
