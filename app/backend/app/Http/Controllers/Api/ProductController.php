<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
class ProductController extends Controller {
 public function index(Request $r){$q=Product::with(['images','category'])->where('is_active',true);if($r->filled('q'))$q->where('name','ilike','%'.$r->q.'%');if($r->filled('category'))$q->whereHas('category',fn($x)=>$x->where('slug',$r->category));return $q->paginate(24);}
 public function show(Product $product){return $product->load(['images','category','inventory']);}
}
