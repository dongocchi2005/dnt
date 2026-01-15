<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('order_items')) {
            return;
        }

        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'variant_id')) {
                $table->unsignedBigInteger('variant_id')->nullable()->after('product_id');
                $table->index(['variant_id']);
            }
            if (!Schema::hasColumn('order_items', 'variant_label')) {
                $table->string('variant_label', 255)->nullable()->after('product_name');
            }
            if (!Schema::hasColumn('order_items', 'variant_sku')) {
                $table->string('variant_sku', 255)->nullable()->after('variant_label');
            }
        });
    }

    public function down(): void
    {
        // Non-destructive: keep columns
    }
};

