<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update Product image paths
        DB::table('products')->where('image', 'like', 'image/%')->update([
            'image' => DB::raw("REPLACE(image, 'image/', 'products/')")
        ]);

        // Update ProductImage paths
        DB::table('product_images')->where('image', 'like', 'image/%')->update([
            'image' => DB::raw("REPLACE(image, 'image/', 'products/')")
        ]);
    }

    public function down(): void
    {
        // Reverse the updates
        DB::table('products')->where('image', 'like', 'products/%')->update([
            'image' => DB::raw("REPLACE(image, 'products/', 'image/')")
        ]);

        DB::table('product_images')->where('image', 'like', 'products/%')->update([
            'image' => DB::raw("REPLACE(image, 'products/', 'image/')")
        ]);
    }
};
