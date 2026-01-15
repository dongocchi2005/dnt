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
            Schema::create('product_variant_values', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_variant_id')->constrained('product_variants')->onDelete('cascade');
                $table->string('name', 255);
                $table->string('value', 255);
                $table->timestamps();
                $table->index(['product_variant_id', 'name']);
            });
            return;
        }

        Schema::table('product_variant_values', function (Blueprint $table) {
            if (!Schema::hasColumn('product_variant_values', 'product_variant_id')) {
                $table->unsignedBigInteger('product_variant_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('product_variant_values', 'name')) {
                $table->string('name', 255)->nullable()->after('product_variant_id');
            }
            if (!Schema::hasColumn('product_variant_values', 'value')) {
                $table->string('value', 255)->nullable()->after('name');
            }
        });

        if (Schema::hasColumn('product_variant_values', 'variant_id') && Schema::hasColumn('product_variant_values', 'product_variant_id')) {
            DB::statement('UPDATE product_variant_values SET product_variant_id = variant_id WHERE product_variant_id IS NULL');
        }

        if (
            Schema::hasColumn('product_variant_values', 'option_id')
            && Schema::hasColumn('product_variant_values', 'option_value_id')
            && Schema::hasTable('product_options')
            && Schema::hasTable('product_option_values')
            && Schema::hasColumn('product_variant_values', 'name')
            && Schema::hasColumn('product_variant_values', 'value')
        ) {
            DB::statement(
                "UPDATE product_variant_values pvv
                 JOIN product_options po ON po.id = pvv.option_id
                 JOIN product_option_values pov ON pov.id = pvv.option_value_id
                 SET pvv.name = COALESCE(pvv.name, po.name),
                     pvv.value = COALESCE(pvv.value, pov.value)
                 WHERE (pvv.name IS NULL OR pvv.value IS NULL)"
            );
        }

        Schema::table('product_variant_values', function (Blueprint $table) {
            if (Schema::hasColumn('product_variant_values', 'product_variant_id')) {
                $table->index(['product_variant_id', 'name']);
            }
        });
    }

    public function down(): void
    {
        // Non-destructive: keep columns/table
    }
};

