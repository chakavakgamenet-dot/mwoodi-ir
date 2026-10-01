<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
class ProductController extends Controller {
 public function index(Request $r){
  $q=Product::with(['images','category','inventory'])->where('is_active',true);
  if($r->filled('q'))$q->where('name','ilike','%'.$r->q.'%');
  if($r->filled('category'))$q->whereHas('category',fn($x)=>$x->where('slug',$r->category)->where('is_active',true));
  if($r->boolean('all')) return $q->orderBy('created_at')->get();
  $perPage=min(max((int)$r->input('per_page',24),1),100);
  return $q->orderBy('created_at')->paginate($perPage);
 }
 public function show(Product $product){return $product->load(['images','category','inventory']);}
}
