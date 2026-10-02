<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    private function guard(Request $r): void
    {
        abort_unless(
            in_array($r->user()->role, ['seller','manager','super_admin'], true),
            403,
            'دسترسی مدیر مورد نیاز است.'
        );
    }

    public function dashboard(Request $r)
    {
        $this->guard($r);

        return [
            'customers' => Customer::where('role','customer')->count(),
            'products' => Product::count(),
            'orders' => Order::count(),
            'pending_orders' => Order::whereIn('order_status',['pending','confirmed'])->count(),
            'sales_today' => (int) Order::whereDate('created_at', today())
                ->where('payment_status','paid')->sum('total'),
        ];
    }

    public function orders(Request $r)
    {
        $this->guard($r);
        return Order::with('items')->latest()->paginate(30);
    }

    public function products(Request $r)
    {
        $this->guard($r);
        return Product::with(['category','inventory'])->latest()->paginate(30);
    }

    public function updateProduct(Request $r, Product $product)
    {
        $this->guard($r);
        $data = $r->validate([
            'is_active' => 'sometimes|boolean',
            'price' => 'sometimes|integer|min:0',
            'name' => 'sometimes|string|max:220',
        ]);
        $product->update($data);
        return $product->fresh(['category','inventory']);
    }

    public function customers(Request $r)
    {
        $this->guard($r);
        return Customer::query()
            ->where('role','customer')
            ->select(['id','name','phone','email','customer_no','national_id','is_active','created_at'])
            ->latest()->paginate(30);
    }

    public function updateCustomer(Request $r, Customer $customer)
    {
        $this->guard($r);
        abort_unless($customer->role === 'customer', 404);

        $data = $r->validate([
            'name' => 'sometimes|required|string|max:160',
            'phone' => ['sometimes','required','string','max:30',Rule::unique('customers','phone')->ignore($customer->id,'id')],
            'email' => ['nullable','email','max:160',Rule::unique('customers','email')->ignore($customer->id,'id')],
            'national_id' => ['nullable','string','max:20',Rule::unique('customers','national_id')->ignore($customer->id,'id')],
            'is_active' => 'sometimes|boolean',
            'password' => 'nullable|string|min:8',
        ]);

        if (array_key_exists('password', $data)) {
            if ($data['password']) $data['password_hash'] = Hash::make($data['password']);
            unset($data['password']);
        }
        $customer->update($data);
        return $customer->fresh();
    }

    public function settings(Request $r)
    {
        $this->guard($r);
        return DB::table('site_settings')->get()->mapWithKeys(
            fn($row) => [$row->key => json_decode($row->value, true)]
        );
    }

    public function updateSettings(Request $r)
    {
        $this->guard($r);

        $data = $r->validate([
            'storefront' => 'sometimes|array',
            'storefront.title' => 'sometimes|string|max:200',
            'storefront.hero_title' => 'sometimes|string|max:500',
            'storefront.hero_text' => 'sometimes|string|max:2000',
            'storefront.about_title' => 'sometimes|string|max:300',
            'storefront.about_text' => 'sometimes|string|max:2000',
            'storefront.address' => 'nullable|string|max:1000',
            'storefront.shipping' => 'nullable|string|max:1000',
            'storefront.contact' => 'nullable|string|max:500',
            'storefront.instagram' => 'nullable|string|max:500',
            'storefront.telegram' => 'nullable|string|max:500',
            'trust' => 'sometimes|array',
            'trust.items' => 'sometimes|array|max:12',
            'trust.items.*' => 'string|max:100',
            'payment' => 'sometimes|array',
            'payment.mode' => 'sometimes|string|max:30',
            'payment.gateway_name' => 'nullable|string|max:100',
            'payment.card_holder' => 'nullable|string|max:160',
            'payment.card_number' => 'nullable|string|max:40',
        ]);

        foreach ($data as $key => $value) {
            DB::table('site_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'updated_at' => now()]
            );
        }

        return $this->settings($r);
    }
}
