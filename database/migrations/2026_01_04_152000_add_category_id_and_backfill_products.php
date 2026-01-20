<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'category_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete()->after('description');
                $table->index('category_id');
            });
        }

        if (!Schema::hasTable('categories')) {
            return;
        }

        if (!Schema::hasColumn('products', 'category')) {
            return;
        }

        $names = DB::table('products')
            ->selectRaw('DISTINCT `category` as name')
            ->whereNotNull('category')
            ->whereRaw("TRIM(`category`) <> ''")
            ->pluck('name')
            ->map(fn($n) => trim($n))
            ->filter()
            ->unique()
            ->values();

        foreach ($names as $name) {
            $exists = DB::table('categories')->where('name', $name)->exists();
            if (!$exists) {
                DB::table('categories')->insert([
                    'name' => $name,
                    'description' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                UPDATE products p
                JOIN categories c ON c.name = p.category
                SET p.category_id = c.id
                WHERE p.category_id IS NULL
            ");
        } else {
            $categoryIdsByName = DB::table('categories')->pluck('id', 'name');
            foreach ($categoryIdsByName as $name => $id) {
                DB::table('products')
                    ->whereNull('category_id')
                    ->where('category', $name)
                    ->update(['category_id' => $id]);
            }
        }
    }

    public function down(): void
    {
        // keep data and column
    }
};
