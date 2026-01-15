<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $path = base_path('database/products_insert_from_excel.sql');
        if (!File::exists($path)) {
            return;
        }

        $sql = File::get($path);
        if (!is_string($sql) || $sql === '') {
            return;
        }

        $hasCategory = Schema::hasColumn('products', 'category');
        $hasCategoryId = Schema::hasColumn('products', 'category_id');

        if ($hasCategory) {
            // Map category_id (text) -> category (text) to match current schema
            $sql = str_replace('`category_id`', '`category`', $sql);
        } elseif (!$hasCategoryId) {
            // Neither column exists; skip import
            return;
        }

        // Use INSERT IGNORE to avoid duplicate slug errors
        $sql = str_replace('INSERT INTO `products`', 'INSERT IGNORE INTO `products`', $sql);

        // Execute import
        DB::unprepared($sql);
    }

    public function down(): void
    {
        // No rollback for imported data
    }
};
