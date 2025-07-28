<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Users
        User::factory(20)->create();
        User::factory()->create([
            'name' => 'Admin',
            'is_admin' => 1,
            'email' => 'admin@example.com',
            'password' => Hash::make('password')
        ]);

        // Categories
        $categories = ['Electronics', 'Clothing', 'Books', 'Home & Garden', 'Sports'];
        foreach ($categories as $cat) {
            Category::create(['name' => $cat, 'slug' => strtolower($cat)]);
        }

        // Brands
        $brands = ['Apple', 'Samsung', 'Nike', 'Adidas', 'Sony'];
        foreach ($brands as $brand) {
            Brand::create(['name' => $brand, 'slug' => strtolower($brand)]);
        }

        // Units
        $units = [
            ['name' => 'Piece', 'short_name' => 'pcs'],
            ['name' => 'Kilogram', 'short_name' => 'kg'],
            ['name' => 'Liter', 'short_name' => 'l'],
            ['name' => 'Box', 'short_name' => 'box'],
            ['name' => 'Meter', 'short_name' => 'm']
        ];
        foreach ($units as $unit) {
            \App\Models\Unit::create([
                'name' => $unit['name'],
                'short_name' => $unit['short_name'],
                'slug' => strtolower($unit['name'])
            ]);
        }

        // Products
        for ($i = 1; $i <= 20; $i++) {
            Product::create([
                'name' => 'Product ' . $i,
                'slug' => 'product-' . $i,
                'item_code' => 'P' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'sales_price' => rand(100000, 1000000),
                'purchase_price' => rand(50000, 500000),
                'inventory_quantity' => rand(10, 100),
                'category_id' => rand(1, 5),
                'brand_id' => rand(1, 5),
                'unit_id' => rand(1, 5),
                'display' => 1,
            ]);
        }

        // News Categories
        $newsCategories = ['Technology', 'Sports', 'Business', 'Health', 'Entertainment'];
        foreach ($newsCategories as $newsCat) {
            NewsCategory::create(['name' => $newsCat, 'slug' => strtolower($newsCat)]);
        }

        // News
        for ($i = 1; $i <= 15; $i++) {
            News::create([
                'name' => 'News Article ' . $i,
                'slug' => 'news-article-' . $i,
                'content' => 'This is the content for news article ' . $i,
                'news_category_id' => rand(1, 5),
            ]);
        }

        // Orders
        $users = User::all();
        for ($i = 1; $i <= 15; $i++) {
            $order = Order::create([
                'invoice_number' => 'INV' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'user_id' => $users->random()->id,
                'total' => rand(100000, 2000000),
                'order_type' => ['Mua tại Cửa hàng', 'Mua Online'][rand(0, 1)],
                'order_status' => ['Chờ xác nhận', 'Đã xác nhận', 'Đang giao hàng', 'Đã giao hàng'][rand(0, 3)],
                'payment_status' => ['Chưa thanh toán', 'Đã thanh toán'][rand(0, 1)],
                'payment_method' => ['COD', 'Chuyển khoản'][rand(0, 1)],
            ]);
            
            // Order Items
            for ($j = 1; $j <= rand(1, 3); $j++) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => rand(1, 20),
                    'quantity' => rand(1, 5),
                    'price' => rand(100000, 500000),
                ]);
            }
        }
    }
}
