<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $p1 = Product::updateOrCreate(
            ['slug' => 'dien-thoai-thanh-ly-mau'],
            [
                'name' => 'Điện thoại thanh lý mẫu',
                'description' => 'Sản phẩm demo dùng để kiểm tra trang thanh lý.',
                'original_price' => 5000000,
                'sale_price' => 1990000,
                'image' => 'image/sample.jpg',
                'stock' => 10,
                'is_active' => true,
                'is_clearance' => true,
            ]
        );

        $p2 = Product::updateOrCreate(
            ['slug' => 'laptop-thanh-ly-mau'],
            [
                'name' => 'Laptop thanh lý mẫu',
                'description' => 'Laptop demo để hiển thị trên trang thanh lý.',
                'original_price' => 15000000,
                'sale_price' => 6990000,
                'image' => 'image/sample2.jpg',
                'stock' => 3,
                'is_active' => true,
                'is_clearance' => true,
            ]
        );

        foreach ([$p1, $p2] as $p) {
            if (!$p) continue;
            if ($p->productImages()->count() === 0 && !empty($p->image)) {
                foreach ([0, 1, 2] as $i) {
                    $p->productImages()->create([
                        'path' => $p->image,
                        'sort_order' => $i + 1,
                    ]);
                }
            }
        }
    }
}
