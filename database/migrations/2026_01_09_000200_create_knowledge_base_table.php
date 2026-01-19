<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::table('knowledge_base', function (Blueprint $table) {
                if (! Schema::hasColumn('knowledge_base', 'title')) {
                    $table->string('title');
                    $table->string('slug')->unique();
                    $table->string('category', 80)->default('general')->index();
                    $table->string('source_type', 40)->default('faq')->index();
                    $table->text('content');
                    $table->json('tags')->nullable();
                    $table->boolean('is_active')->default(true)->index();
                }
            });

            return;
        } catch (\Throwable) {
        }

        try {
            Schema::create('knowledge_base', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
            });
        } catch (\Throwable) {
        }

        Schema::table('knowledge_base', function (Blueprint $table) {
            if (! Schema::hasColumn('knowledge_base', 'title')) {
                $table->string('title');
                $table->string('slug')->unique();
                $table->string('category', 80)->default('general')->index();
                $table->string('source_type', 40)->default('faq')->index();
                $table->text('content');
                $table->json('tags')->nullable();
                $table->boolean('is_active')->default(true)->index();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_base');
    }
};
