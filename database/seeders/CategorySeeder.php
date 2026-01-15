<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Điện thoại',
                'description' => 'Các loại điện thoại thông minh và cơ bản',
            ],
            [
                'name' => 'Laptop',
                'description' => 'Máy tính xách tay các hãng',
            ],
            [
                'name' => 'Phụ kiện',
                'description' => 'Phụ kiện công nghệ và điện tử',
            ],
            [
                'name' => 'Tai nghe',
                'description' => 'Tai nghe có dây và không dây',
            ],
            [
                'name' => 'Sạc dự phòng',
                'description' => 'Pin sạc dự phòng các dung lượng',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
