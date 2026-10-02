<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller {
 private function normalizeDigits(string $value): string {
  return strtr(trim($value), ['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9']);
 }
 public function register(Request $r){
  $data=$r->validate([
   'name'=>'required|string|max:160',
   'phone'=>'required|string|max:30|unique:customers,phone',
   'national_id'=>'nullable|string|max:20|unique:customers,national_id',
   'password'=>'required|string|min:8',
  ]);
  $data['phone']=$this->normalizeDigits($data['phone']);
  $data['national_id']=$this->normalizeDigits((string)($data['national_id'] ?? ''));
  $nationalId=trim((string)($data['national_id'] ?? '')) ?: null;
  $customerNo='CUS-'.str_pad((string)(Customer::where('role','customer')->count()+1),4,'0',STR_PAD_LEFT);
  while(Customer::where('customer_no',$customerNo)->exists()){
   $customerNo='CUS-'.str_pad((string)(Customer::where('role','customer')->count()+1+random_int(1,99)),4,'0',STR_PAD_LEFT);
  }
  $c=Customer::create([
   'name'=>$data['name'],'phone'=>trim($data['phone']),'national_id'=>$nationalId,
   'customer_no'=>$customerNo,'password_hash'=>Hash::make($data['password']),'role'=>'customer','is_active'=>true
  ]);
  $token=$c->createToken('web')->plainTextToken;
  return response()->json(['customer'=>$c,'token'=>$token],201);
 }
 public function login(Request $r){
  $data=$r->validate(['identifier'=>'required|string','password'=>'required|string']);
  $identifier=$this->normalizeDigits($data['identifier']);
  $c=Customer::where(function($q) use($identifier){
      $q->where('phone',$identifier)->orWhere('national_id',$identifier)->orWhere('customer_no',$identifier);
    })->where('role','customer')->where('is_active',true)->first();
  if(!$c||!Hash::check($data['password'],$c->password_hash)) throw ValidationException::withMessages(['identifier'=>'اطلاعات ورود صحیح نیست.']);
  $token=$c->createToken('web',['customer'])->plainTextToken;
  return ['customer'=>$c,'token'=>$token];
 }
 public function adminLogin(Request $r){
  $data=$r->validate(['username'=>'required|string|max:160','password'=>'required|digits:8']);
  $username = $this->normalizeDigits((string)$data['username']);
  $c=Customer::where(function($q) use ($username){
      $q->where('phone',$username)->orWhere('email',$username)->orWhere('customer_no',$username);
    })->whereIn('role',['seller','manager','super_admin'])->where('is_active',true)->first();
  if(!$c||!Hash::check($data['password'],$c->password_hash)) throw ValidationException::withMessages(['username'=>'نام کاربری یا رمز عبور صحیح نیست.']);
  $token=$c->createToken('admin-web',['admin'])->plainTextToken;
  return ['customer'=>$c,'token'=>$token];
 }
 public function updateProfile(Request $r){
  $c=$r->user();
  abort_unless($c->role==='customer',403);
  if($r->has('phone')) $r->merge(['phone'=>$this->normalizeDigits((string)$r->input('phone'))]);
  if($r->has('national_id')) $r->merge(['national_id'=>$this->normalizeDigits((string)$r->input('national_id'))]);
  $data=$r->validate([
   'name'=>'sometimes|required|string|max:160',
   'phone'=>'sometimes|required|string|max:30|unique:customers,phone,'.$c->id.',id',
   'national_id'=>'nullable|string|max:20|unique:customers,national_id,'.$c->id.',id',
   'password'=>'nullable|string|min:8',
  ]);
  if(array_key_exists('password',$data)){
   if($data['password']) $data['password_hash']=Hash::make($data['password']);
   unset($data['password']);
  }
  $c->update($data);
  return ['customer'=>$c->fresh()];
 }
 public function logout(Request $r){$r->user()->currentAccessToken()?->delete();return ['ok'=>true];}
 public function me(Request $r){return ['customer'=>$r->user()];}
}
