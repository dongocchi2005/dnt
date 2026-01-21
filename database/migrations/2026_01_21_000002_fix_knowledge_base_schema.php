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
                $table->string('title')->nullable();
                $table->string('slug')->nullable()->index();
                $table->string('category', 80)->nullable()->default('general')->index();
                $table->string('source_type', 40)->nullable()->default('faq')->index();
                $table->text('content')->nullable();
                $table->json('tags')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });

            return;
        }

        $hasTitle = Schema::hasColumn('knowledge_base', 'title');
        $hasSlug = Schema::hasColumn('knowledge_base', 'slug');
        $hasCategory = Schema::hasColumn('knowledge_base', 'category');
        $hasSourceType = Schema::hasColumn('knowledge_base', 'source_type');
        $hasContent = Schema::hasColumn('knowledge_base', 'content');
        $hasTags = Schema::hasColumn('knowledge_base', 'tags');
        $hasIsActive = Schema::hasColumn('knowledge_base', 'is_active');

        Schema::table('knowledge_base', function (Blueprint $table) use (
            $hasTitle,
            $hasSlug,
            $hasCategory,
            $hasSourceType,
            $hasContent,
            $hasTags,
            $hasIsActive
        ) {
            if (!$hasTitle) {
                $table->string('title')->nullable();
            }

            if (!$hasSlug) {
                $table->string('slug')->nullable()->index();
            }

            if (!$hasCategory) {
                $table->string('category', 80)->nullable()->default('general')->index();
            }

            if (!$hasSourceType) {
                $table->string('source_type', 40)->nullable()->default('faq')->index();
            }

            if (!$hasContent) {
                $table->text('content')->nullable();
            }

            if (!$hasTags) {
                $table->json('tags')->nullable();
            }

            if (!$hasIsActive) {
                $table->boolean('is_active')->default(true)->index();
            }
        });

        if ($hasSlug || Schema::hasColumn('knowledge_base', 'slug')) {
            $hasDuplicateSlug = DB::table('knowledge_base')
                ->select('slug')
                ->whereNotNull('slug')
                ->groupBy('slug')
                ->havingRaw('COUNT(*) > 1')
                ->limit(1)
                ->exists();

            if (!$hasDuplicateSlug && !$this->hasIndex('knowledge_base', 'knowledge_base_slug_unique')) {
                Schema::table('knowledge_base', function (Blueprint $table) {
                    $table->unique('slug', 'knowledge_base_slug_unique');
                });
            }
        }
    }

    public function down(): void
    {
        // Non-destructive
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        if (DB::getDriverName() !== 'mysql') {
            return false;
        }

        $dbName = DB::getDatabaseName();
        return DB::table('information_schema.statistics')
            ->where('table_schema', $dbName)
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();
    }
};
