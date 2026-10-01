<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Order,Inventory,Customer};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OrderController extends Controller {
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
   'items'=>'nullable|array',
   'items.*.product_id'=>'required_with:items|uuid',
   'items.*.quantity'=>'required_with:items|integer|min:1|max:100',
  ]);

  $u=$r->user();
  if(!$u){
   $name=$data['guest_name'] ?? $data['shipping_address']['recipient_name'];
   $phone=$data['guest_phone'] ?? $data['shipping_address']['recipient_phone'];
   $u=Customer::where('phone',$phone)->first();
   if(!$u){
    $u=Customer::create([
      'name'=>$name,
      'phone'=>$phone,
      'password_hash'=>Hash::make(bin2hex(random_bytes(16))),
      'role'=>'customer',
      'is_active'=>true,
    ]);
   }
  }

  return DB::transaction(function() use($u,$data){
   $items=[];
   if(!empty($data['items'])){
    foreach($data['items'] as $line){
     $product=\App\Models\Product::with('inventory')->findOrFail($line['product_id']);
     $quantity=(int)$line['quantity'];
     $inv=Inventory::where('product_id',$product->id)->lockForUpdate()->first();
     abort_if(!$product->is_active || !$inv || ($inv->quantity-$inv->reserved_quantity)<$quantity,422,'موجودی محصول کافی نیست.');
     $items[]=['product_id'=>$product->id,'product_name_snapshot'=>$product->name,'sku_snapshot'=>$product->sku,'unit_price'=>$product->price,'quantity'=>$quantity,'total_price'=>$product->price*$quantity];
     $inv->reserved_quantity += $quantity;
     $inv->save();
    }
   } else {
    $cart=$u->cart()->with('items.product.inventory')->first();
    abort_if(!$cart || $cart->items->isEmpty(),422,'سبد خرید خالی است.');
    foreach($cart->items as $ci){
     $p=$ci->product;
     $inv=Inventory::where('product_id',$p->id)->lockForUpdate()->first();
     abort_if(!$inv || ($inv->quantity-$inv->reserved_quantity)<$ci->quantity,422,'موجودی محصول کافی نیست.');
     $items[]=['product_id'=>$p->id,'product_name_snapshot'=>$p->name,'sku_snapshot'=>$p->sku,'unit_price'=>$p->price,'quantity'=>$ci->quantity,'total_price'=>$p->price*$ci->quantity];
     $inv->reserved_quantity += $ci->quantity;
     $inv->save();
    }
   }

   $subtotal=array_sum(array_column($items,'total_price'));
   $o=Order::create([
    'order_number'=>'MW-'.now()->format('ymdHis').'-'.random_int(10,99),
    'customer_id'=>$u->id,
    'customer_name_snapshot'=>$u->name,
    'customer_phone_snapshot'=>$u->phone,
    'shipping_address_snapshot'=>$data['shipping_address'],
    'shipping_method'=>$data['shipping_method'],
    'subtotal'=>$subtotal,
    'total'=>$subtotal,
   ]);
   $o->items()->createMany($items);
   if(empty($data['items']) && $u->cart) $u->cart->items()->delete();
   return response()->json($o->load('items'),201);
  });
 }
}
