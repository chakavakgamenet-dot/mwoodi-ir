<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\AdminController;

Route::prefix('v1')->group(function () {
 Route::get('/health', function () {
  try { DB::select('select 1'); return response()->json(['ok'=>true,'service'=>'mwoodi-api','database'=>'ok','time'=>now()->toIso8601String()]); }
  catch (Throwable $e) { return response()->json(['ok'=>false,'service'=>'mwoodi-api','database'=>'error'],503); }
 });
 Route::post('/auth/register',[AuthController::class,'register']);
 Route::post('/auth/login',[AuthController::class,'login']);
 Route::post('/auth/admin-login',[AuthController::class,'adminLogin']);
 Route::get('/settings', function(){ return DB::table('site_settings')->get()->mapWithKeys(function($row){ return [$row->key => json_decode($row->value, true)]; }); });
 Route::get('/products',[ProductController::class,'index']);
 Route::get('/products/{product:slug}',[ProductController::class,'show']);
 Route::post('/orders',[OrderController::class,'store']);
 Route::get('/orders/guest-lookup',[OrderController::class,'guestLookup']);
 Route::middleware('auth:sanctum')->group(function(){
  Route::post('/auth/logout',[AuthController::class,'logout']);
  Route::get('/auth/me',[AuthController::class,'me']);
  Route::patch('/auth/profile',[AuthController::class,'updateProfile']);
  Route::get('/cart',[CartController::class,'show']);
  Route::post('/cart/items',[CartController::class,'store']);
  Route::patch('/cart/items/{product:id}',[CartController::class,'update']);
  Route::delete('/cart/items/{product:id}',[CartController::class,'destroy']);
  Route::get('/orders',[OrderController::class,'index']);
  Route::get('/orders/{order:order_number}',[OrderController::class,'show']);
  Route::prefix('admin')->group(function(){
   Route::get('/dashboard',[AdminController::class,'dashboard']);
   Route::get('/orders',[AdminController::class,'orders']);
   Route::get('/products',[AdminController::class,'products']);
   Route::patch('/products/{product:id}',[AdminController::class,'updateProduct']);
   Route::get('/customers',[AdminController::class,'customers']);
   Route::patch('/customers/{customer:id}',[AdminController::class,'updateCustomer']);
   Route::get('/settings',[AdminController::class,'settings']);
   Route::put('/settings',[AdminController::class,'updateSettings']);
  });
 });
});
