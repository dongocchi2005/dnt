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

        if (!Schema::hasTable('product_variants') || !Schema::hasTable('products')) {
            return;
        }
        if (!Schema::hasColumn('product_variants', 'stock') || !Schema::hasColumn('products', 'stock')) {
            return;
        }

        DB::statement(
            "UPDATE product_variants pv
             JOIN products p ON p.id = pv.product_id
             SET pv.stock = p.stock
             WHERE (pv.stock IS NULL OR pv.stock = 0)
               AND p.stock IS NOT NULL
               AND p.stock > 0"
        );
    }

    public function down(): void
    {
        // keep
    }
};
