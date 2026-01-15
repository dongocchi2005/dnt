<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product_variants')) {
            Schema::create('product_variants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
                $table->string('sku')->nullable()->unique();
                $table->decimal('price', 10, 2)->nullable();
                $table->decimal('sale_price', 10, 2)->nullable();
                $table->integer('stock')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->index(['product_id', 'is_active']);
            });
        } else {
            Schema::table('product_variants', function (Blueprint $table) {
                if (!Schema::hasColumn('product_variants', 'sku')) {
                    $table->string('sku')->nullable()->unique()->after('product_id');
                }
                if (!Schema::hasColumn('product_variants', 'price')) {
                    $table->decimal('price', 10, 2)->nullable()->after('sku');
                }
                if (!Schema::hasColumn('product_variants', 'sale_price')) {
                    $table->decimal('sale_price', 10, 2)->nullable()->after('price');
                }
                if (!Schema::hasColumn('product_variants', 'stock')) {
                    $table->integer('stock')->default(0)->after('sale_price');
                }
                if (!Schema::hasColumn('product_variants', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('stock');
                }
            });
        }

        if (Schema::hasTable('product_variants')) {
            if (Schema::hasColumn('product_variants', 'original_price') && Schema::hasColumn('product_variants', 'price')) {
                DB::statement("UPDATE product_variants SET price = original_price WHERE price IS NULL AND original_price IS NOT NULL");
            }
            if (Schema::hasColumn('product_variants', 'sale_price') && Schema::hasColumn('product_variants', 'original_price')) {
                DB::statement("UPDATE product_variants SET sale_price = NULL WHERE sale_price = 0");
            }
            if (Schema::hasColumn('product_variants', 'status') && Schema::hasColumn('product_variants', 'is_active')) {
                DB::statement("UPDATE product_variants SET is_active = CASE WHEN status = 'active' THEN 1 ELSE 0 END WHERE status IS NOT NULL");
            }
        }
    }

    public function down(): void
    {
        // Non-destructive: keep columns/table
    }
};
