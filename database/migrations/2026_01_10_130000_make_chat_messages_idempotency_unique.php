<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('chat_messages')) {
            return;
        }

        if (!Schema::hasColumn('chat_messages', 'idempotency_key')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                DELETE cm1 FROM chat_messages cm1
                INNER JOIN chat_messages cm2
                    ON cm1.chat_session_id = cm2.chat_session_id
                    AND cm1.idempotency_key = cm2.idempotency_key
                    AND cm1.id > cm2.id
                WHERE cm1.idempotency_key IS NOT NULL
            ");
        }

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->unique(['chat_session_id', 'idempotency_key'], 'chat_messages_session_idempotency_unique');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('chat_messages')) {
            return;
        }

        Schema::table('chat_messages', function (Blueprint $table) {
            try {
                $table->dropUnique('chat_messages_session_idempotency_unique');
            } catch (\Throwable $e) {
            }
        });
    }
};
