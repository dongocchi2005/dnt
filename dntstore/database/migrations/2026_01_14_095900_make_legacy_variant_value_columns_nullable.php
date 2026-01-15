<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product_variant_values')) {
            return;
        }

        if (Schema::hasColumn('product_variant_values', 'variant_id')) {
            DB::statement('ALTER TABLE product_variant_values MODIFY variant_id BIGINT UNSIGNED NULL');
        }
        if (Schema::hasColumn('product_variant_values', 'option_id')) {
            DB::statement('ALTER TABLE product_variant_values MODIFY option_id BIGINT UNSIGNED NULL');
        }
        if (Schema::hasColumn('product_variant_values', 'option_value_id')) {
            DB::statement('ALTER TABLE product_variant_values MODIFY option_value_id BIGINT UNSIGNED NULL');
        }
    }

    public function down(): void
    {
        // keep
    }
};

