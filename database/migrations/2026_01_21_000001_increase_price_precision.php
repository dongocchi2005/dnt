<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        if (Schema::hasTable('products')) {
            if (Schema::hasColumn('products', 'original_price')) {
                DB::statement('ALTER TABLE `products` MODIFY `original_price` DECIMAL(15,2) NOT NULL');
            }
            if (Schema::hasColumn('products', 'sale_price')) {
                DB::statement('ALTER TABLE `products` MODIFY `sale_price` DECIMAL(15,2) NOT NULL');
            }
        }

        if (Schema::hasTable('product_variants')) {
            if (Schema::hasColumn('product_variants', 'price')) {
                DB::statement('ALTER TABLE `product_variants` MODIFY `price` DECIMAL(15,2) NULL');
            }
            if (Schema::hasColumn('product_variants', 'original_price')) {
                DB::statement('ALTER TABLE `product_variants` MODIFY `original_price` DECIMAL(15,2) NULL');
            }
            if (Schema::hasColumn('product_variants', 'sale_price')) {
                DB::statement('ALTER TABLE `product_variants` MODIFY `sale_price` DECIMAL(15,2) NULL');
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        if (Schema::hasTable('products')) {
            if (Schema::hasColumn('products', 'original_price')) {
                DB::statement('ALTER TABLE `products` MODIFY `original_price` DECIMAL(10,2) NOT NULL');
            }
            if (Schema::hasColumn('products', 'sale_price')) {
                DB::statement('ALTER TABLE `products` MODIFY `sale_price` DECIMAL(10,2) NOT NULL');
            }
        }

        if (Schema::hasTable('product_variants')) {
            if (Schema::hasColumn('product_variants', 'price')) {
                DB::statement('ALTER TABLE `product_variants` MODIFY `price` DECIMAL(10,2) NULL');
            }
            if (Schema::hasColumn('product_variants', 'original_price')) {
                DB::statement('ALTER TABLE `product_variants` MODIFY `original_price` DECIMAL(10,2) NULL');
            }
            if (Schema::hasColumn('product_variants', 'sale_price')) {
                DB::statement('ALTER TABLE `product_variants` MODIFY `sale_price` DECIMAL(10,2) NULL');
            }
        }
    }
};

