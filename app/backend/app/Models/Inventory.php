<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Inventory extends Model {protected $table='inventory';protected $primaryKey='product_id';public $incrementing=false;protected $guarded=[];public function product(){return $this->belongsTo(Product::class,'product_id');} }
