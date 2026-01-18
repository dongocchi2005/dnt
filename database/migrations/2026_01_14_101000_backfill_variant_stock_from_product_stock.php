<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product_variants') || !Schema::hasTable('products')) {
            return;
        }
        if (!Schema::hasColumn('product_variants', 'stock') || !Schema::hasColumn('products', 'stock')) {
            return;
        }

        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            DB::statement(
                "UPDATE product_variants
                 SET stock = (SELECT stock FROM products WHERE products.id = product_variants.product_id)
                 WHERE (stock IS NULL OR stock = 0)
                   AND EXISTS (
                       SELECT 1 FROM products 
                       WHERE products.id = product_variants.product_id 
                       AND products.stock IS NOT NULL 
                       AND products.stock > 0
                   )"
            );
        } else {
            DB::statement(
                "UPDATE product_variants pv
                 JOIN products p ON p.id = pv.product_id
                 SET pv.stock = p.stock
                 WHERE (pv.stock IS NULL OR pv.stock = 0)
                   AND p.stock IS NOT NULL
                   AND p.stock > 0"
            );
        }
    }

    public function down(): void
    {
        // keep
    }
};

