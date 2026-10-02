<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Order,Inventory,Customer};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller {
 private function normalizeDigits(string $value): string {
  return strtr(trim($value), ['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9']);
 }
 public function index(Request $r){
  return Order::with('items')->where('customer_id',$r->user()->id)->latest()->paginate(20);
 }

 public function show(Request $r, Order $order){
  abort_unless($order->customer_id === $r->user()->id,403);
  return $order->load('items');
 }

 public function store(Request $r){
  $data=$r->validate([
   'shipping_address'=>'required|array',
   'shipping_address.recipient_name'=>'required|string|max:160',
   'shipping_address.recipient_phone'=>'required|string|max:30',
   'shipping_address.province'=>'required|string|max:100',
   'shipping_address.city'=>'required|string|max:100',
   'shipping_address.address'=>'required|string|max:5000',
   'shipping_address.postal_code'=>'nullable|string|max:20',
   'shipping_method'=>'required|string|max:100',
   'guest_name'=>'nullable|string|max:160',
   'guest_phone'=>'nullable|string|max:30',
   'items'=>'nullable|array|min:1',
   'items.*.product_id'=>'required_with:items|uuid',
   'items.*.quantity'=>'required_with:items|integer|min:1|max:100',
  ]);

  $u=$r->user();
  $isGuest=!$u;
  if(isset($data['guest_phone'])) $data['guest_phone']=$this->normalizeDigits($data['guest_phone']);
  $data['shipping_address']['recipient_phone']=$this->normalizeDigits($data['shipping_address']['recipient_phone']);
  if($isGuest && empty($data['items'])){
   return response()->json(['message'=>'برای خرید میهمان، اقلام سبد باید ارسال شوند.'],422);
  }

  return DB::transaction(function() use($u,$isGuest,$data){
   $items=[];
   if(!empty($data['items'])){
    foreach($data['items'] as $line){
     $product=\App\Models\Product::with('inventory')->where('is_active',true)->findOrFail($line['product_id']);
     $quantity=(int)$line['quantity'];
     $inv=Inventory::where('product_id',$product->id)->lockForUpdate()->first();
     abort_if(!$inv || ($inv->quantity-$inv->reserved_quantity)<$quantity,422,'موجودی محصول کافی نیست.');
     $items[]=['product_id'=>$product->id,'product_name_snapshot'=>$product->name,'sku_snapshot'=>$product->sku,'unit_price'=>$product->price,'quantity'=>$quantity,'total_price'=>$product->price*$quantity];
     $inv->reserved_quantity += $quantity;
     $inv->save();
    }
   } else {
    $cart=$u->cart()->with('items.product.inventory')->first();
    abort_if(!$cart || $cart->items->isEmpty(),422,'سبد خرید خالی است.');
    foreach($cart->items as $ci){
     $p=$ci->product;
     abort_if(!$p || !$p->is_active,422,'یکی از محصولات دیگر در دسترس نیست.');
     $inv=Inventory::where('product_id',$p->id)->lockForUpdate()->first();
     abort_if(!$inv || ($inv->quantity-$inv->reserved_quantity)<$ci->quantity,422,'موجودی محصول کافی نیست.');
     $items[]=['product_id'=>$p->id,'product_name_snapshot'=>$p->name,'sku_snapshot'=>$p->sku,'unit_price'=>$p->price,'quantity'=>$ci->quantity,'total_price'=>$p->price*$ci->quantity];
     $inv->reserved_quantity += $ci->quantity;
     $inv->save();
    }
   }

   $subtotal=array_sum(array_column($items,'total_price'));
   $name=$u?->name ?? ($data['guest_name'] ?? $data['shipping_address']['recipient_name']);
   $phone=$u?->phone ?? ($data['guest_phone'] ?? $data['shipping_address']['recipient_phone']);
   $o=Order::create([
    'order_number'=>'MW-'.now()->format('ymdHis').'-'.random_int(100,999),
    'customer_id'=>$u?->id,
    'customer_name_snapshot'=>$name,
    'customer_phone_snapshot'=>$phone,
    'shipping_address_snapshot'=>$data['shipping_address'],
    'shipping_method'=>$data['shipping_method'],
    'subtotal'=>$subtotal,
    'total'=>$subtotal,
    'payment_status'=>'unpaid',
    'order_status'=>'pending',
   ]);
   $o->items()->createMany($items);
   if(!$isGuest && $u->cart) $u->cart->items()->delete();
   return response()->json($o->load('items'),201);
  });
 }

 public function guestLookup(Request $r){
  $data=$r->validate(['order_number'=>'required|string|max:32','phone'=>'required|string|max:30']);
  $phone=$this->normalizeDigits($data['phone']);
  return Order::with('items')
   ->where('order_number',$data['order_number'])
   ->where('customer_phone_snapshot',$phone)
   ->firstOrFail();
 }
}
