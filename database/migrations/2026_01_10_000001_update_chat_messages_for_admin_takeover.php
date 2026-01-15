<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_messages', 'meta')) {
                $table->json('meta')->nullable()->after('content');
            }
        });

        if (Schema::hasColumn('chat_messages', 'role') && DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE chat_messages MODIFY role ENUM('user','assistant','system','admin') NULL");
        }
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            if (Schema::hasColumn('chat_messages', 'meta')) {
                $table->dropColumn('meta');
            }
        });

        if (Schema::hasColumn('chat_messages', 'role') && DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE chat_messages MODIFY role ENUM('user','assistant','system') NULL");
        }
    }
};
