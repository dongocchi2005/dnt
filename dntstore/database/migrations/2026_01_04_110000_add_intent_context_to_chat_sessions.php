<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_sessions', 'last_intent')) {
                $table->string('last_intent')->nullable()->after('title');
            }
            if (!Schema::hasColumn('chat_sessions', 'context')) {
                $table->json('context')->nullable()->after('last_intent');
            }
        });
    }

    public function down(): void
    {
        Schema::table('chat_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('chat_sessions', 'context')) {
                $table->dropColumn('context');
            }
            if (Schema::hasColumn('chat_sessions', 'last_intent')) {
                $table->dropColumn('last_intent');
            }
        });
    }
};
