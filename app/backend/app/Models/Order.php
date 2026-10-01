<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Order extends Model {public $incrementing=false;protected $keyType='string';protected $fillable=['order_number','customer_id','customer_name_snapshot','customer_phone_snapshot','shipping_address_snapshot','subtotal','discount','shipping_cost','total','payment_status','order_status','shipping_method','tracking_code','notes'];protected $casts=['shipping_address_snapshot'=>'array'];public function customer(){return $this->belongsTo(Customer::class);}public function items(){return $this->hasMany(OrderItem::class);}}
