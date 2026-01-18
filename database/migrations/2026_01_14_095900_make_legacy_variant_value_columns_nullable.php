<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product_variant_values')) {
            return;
        }

        Schema::table('product_variant_values', function (Blueprint $table) {
            if (Schema::hasColumn('product_variant_values', 'variant_id')) {
                $table->unsignedBigInteger('variant_id')->nullable()->change();
            }
            if (Schema::hasColumn('product_variant_values', 'option_id')) {
                $table->unsignedBigInteger('option_id')->nullable()->change();
            }
            if (Schema::hasColumn('product_variant_values', 'option_value_id')) {
                $table->unsignedBigInteger('option_value_id')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        // keep
    }
};

