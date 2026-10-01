<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable; use Laravel\Sanctum\HasApiTokens; use Illuminate\Notifications\Notifiable;
class Customer extends Authenticatable {use HasApiTokens,Notifiable;protected $table='customers';protected $keyType='string';public $incrementing=false;protected $fillable=['name','phone','email','password_hash','role','is_active','customer_no','national_id'];protected $hidden=['password_hash'];public function getAuthPassword(){return $this->password_hash;}public function orders(){return $this->hasMany(Order::class);}public function cart(){return $this->hasOne(Cart::class);}}
