<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Product extends Model {public $incrementing=false;protected $keyType='string';public function getRouteKeyName(){return 'slug';}protected $fillable=['category_id','name','slug','description','price','compare_at_price','sku','is_active'];public function images(){return $this->hasMany(ProductImage::class)->orderBy('sort_order');}public function category(){return $this->belongsTo(Category::class);}public function inventory(){return $this->hasOne(Inventory::class);}}
