<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'variants_json')) {
            Schema::table('products', function (Blueprint $table) {
                $table->json('variants_json')->nullable()->after('image');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'variants_json')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('variants_json');
            });
        }
    }
};
