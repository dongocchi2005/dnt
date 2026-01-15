<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('chat_messages')) {
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        if (Schema::hasColumn('chat_messages', 'message')) {
            DB::statement("ALTER TABLE chat_messages MODIFY message TEXT NULL");
        }

        if (Schema::hasColumn('chat_messages', 'sender')) {
            DB::statement("ALTER TABLE chat_messages MODIFY sender ENUM('user','bot','admin') NULL");
        }
    }

    public function down(): void
    {
    }
};

