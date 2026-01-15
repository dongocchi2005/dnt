<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product_variants')) {
            return;
        }
        Schema::table('product_variants', function (Blueprint $table) {
            if (!Schema::hasColumn('product_variants', 'color')) {
                $table->string('color')->nullable()->after('variant_name');
            }
            if (!Schema::hasColumn('product_variants', 'size')) {
                $table->string('size')->nullable()->after('color');
            }
            if (!Schema::hasColumn('product_variants', 'sku')) {
                $table->string('sku')->nullable()->unique()->after('size');
            }
        });
    }

    public function down(): void
    {
        // keep columns
    }
};
