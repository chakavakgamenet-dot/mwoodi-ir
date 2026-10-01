<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{Cart,Product};
use Illuminate\Http\Request;

class CartController extends Controller{
 private function cart($u){return Cart::firstOrCreate(['customer_id'=>$u->id]);}
 public function show(Request $r){return $this->cart($r->user())->load('items.product.images');}
 public function store(Request $r){
  $d=$r->validate(['product_id'=>'required|uuid','quantity'=>'required|integer|min:1|max:100']);
  $product=Product::with('inventory')->where('is_active',true)->findOrFail($d['product_id']);
  $available=(int)($product->inventory?->quantity ?? 0)-(int)($product->inventory?->reserved_quantity ?? 0);
  $cart=$this->cart($r->user());
  $i=$cart->items()->firstOrNew(['product_id'=>$product->id]);
  $next=($i->quantity??0)+(int)$d['quantity'];
  abort_if($available<$next,422,'موجودی محصول برای این تعداد کافی نیست.');
  $i->quantity=$next;$i->save();
  return $cart->load('items.product.images');
 }
 public function update(Request $r,Product $product){
  $d=$r->validate(['quantity'=>'required|integer|min:1|max:100']);
  abort_unless($product->is_active,422,'این محصول دیگر فعال نیست.');
  $product->load('inventory');
  $available=(int)($product->inventory?->quantity ?? 0)-(int)($product->inventory?->reserved_quantity ?? 0);
  abort_if($available<(int)$d['quantity'],422,'موجودی محصول برای این تعداد کافی نیست.');
  $cart=$this->cart($r->user());$cart->items()->where('product_id',$product->id)->update(['quantity'=>$d['quantity']]);
  return $cart->load('items.product.images');
 }
 public function destroy(Request $r,Product $product){$this->cart($r->user())->items()->where('product_id',$product->id)->delete();return $this->show($r);}
}
