<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class AdminController extends Controller {
 private function guard(Request $r): void {
  abort_unless(in_array($r->user()->role, ['seller','manager','super_admin'], true), 403, 'دسترسی مدیر مورد نیاز است.');
 }
 public function dashboard(Request $r){
  $this->guard($r);
  return [
   'customers'=>Customer::where('role','customer')->count(),
   'products'=>Product::count(),
   'orders'=>Order::count(),
   'pending_orders'=>Order::whereIn('order_status',['pending','confirmed'])->count(),
   'sales_today'=>(int) Order::whereDate('created_at',today())->where('payment_status','paid')->sum('total'),
  ];
 }
 public function orders(Request $r){
  $this->guard($r);
  return Order::with('items')->latest()->paginate(30);
 }
 public function products(Request $r){
  $this->guard($r);
  return Product::with(['category','inventory'])->latest()->paginate(30);
 }
 public function updateProduct(Request $r, Product $product){
  $this->guard($r);
  $data=$r->validate(['is_active'=>'sometimes|boolean','price'=>'sometimes|integer|min:0','name'=>'sometimes|string|max:220']);
  $product->update($data);
  return $product->fresh(['category','inventory']);
 }
}
