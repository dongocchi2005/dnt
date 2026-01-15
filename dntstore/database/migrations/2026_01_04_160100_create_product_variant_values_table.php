<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product_variant_values')) {
            Schema::create('product_variant_values', function (Blueprint $table) {
                $table->id();
                $table->foreignId('variant_id')->constrained('product_variants')->onDelete('cascade');
                $table->foreignId('option_id')->constrained('product_options')->onDelete('cascade');
                $table->foreignId('option_value_id')->constrained('product_option_values')->onDelete('cascade');
                $table->timestamps();

                $table->unique(['variant_id', 'option_value_id']);
            });
        }
    }

    public function down(): void
    {
        // keep data
    }
};
