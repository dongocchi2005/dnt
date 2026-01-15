<?php

namespace Database\Seeders;
use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
 

public function run(): void
{
    Service::create([
        'name' => 'Sửa tivi',
        'description' => 'Sửa chữa các lỗi tivi LCD, LED',
        'price' => 300000,
    ]);

    Service::create([
        'name' => 'Sửa máy giặt',
        'description' => 'Sửa máy giặt cửa trên, cửa trước',
        'price' => 250000,
    ]);

    Service::create([
        'name' => 'Sửa tủ lạnh',
        'description' => 'Sửa tủ lạnh không lạnh, rò gas',
        'price' => 400000,
    ]);
}

}
