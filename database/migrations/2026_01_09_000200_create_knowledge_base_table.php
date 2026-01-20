<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('knowledge_base')) {
            Schema::create('knowledge_base', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->string('category', 80)->default('general')->index();
                $table->string('source_type', 40)->default('faq')->index();
                $table->text('content');
                $table->json('tags')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });

            return;
        }

        $existingColumns = Schema::getColumnListing('knowledge_base');

        Schema::table('knowledge_base', function (Blueprint $table) use ($existingColumns) {
            if (!in_array('title', $existingColumns, true)) {
                $table->string('title')->nullable();
            }

            if (!in_array('slug', $existingColumns, true)) {
                $table->string('slug')->nullable();
            }

            if (!in_array('category', $existingColumns, true)) {
                $table->string('category', 80)->nullable()->default('general')->index();
            }

            if (!in_array('source_type', $existingColumns, true)) {
                $table->string('source_type', 40)->nullable()->default('faq')->index();
            }

            if (!in_array('content', $existingColumns, true)) {
                $table->text('content')->nullable();
            }

            if (!in_array('tags', $existingColumns, true)) {
                $table->json('tags')->nullable();
            }

            if (!in_array('is_active', $existingColumns, true)) {
                $table->boolean('is_active')->default(true)->index();
            }
        });

        if (!in_array('slug', $existingColumns, true) && !DB::table('knowledge_base')->exists()) {
            Schema::table('knowledge_base', function (Blueprint $table) {
                $table->unique('slug');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_base');
    }
};
