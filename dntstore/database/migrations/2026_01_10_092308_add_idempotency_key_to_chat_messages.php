<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->string('idempotency_key')->nullable()->after('content');
            $table->index(['chat_session_id', 'idempotency_key']);
        });
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropIndex(['chat_session_id', 'idempotency_key']);
            $table->dropColumn('idempotency_key');
        });
    }
};
