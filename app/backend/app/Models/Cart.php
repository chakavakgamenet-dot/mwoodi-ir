<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Cart extends Model {public $incrementing=false;protected $keyType='string';protected $guarded=[];public function items(){return $this->hasMany(CartItem::class);} }
