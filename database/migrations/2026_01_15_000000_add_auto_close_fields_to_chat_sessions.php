<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_sessions', 'closed_at')) {
                $table->timestamp('closed_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('chat_sessions', 'last_staff_message_at')) {
                $table->timestamp('last_staff_message_at')->nullable()->after('closed_at');
            }
            if (!Schema::hasColumn('chat_sessions', 'last_customer_message_at')) {
                $table->timestamp('last_customer_message_at')->nullable()->after('last_staff_message_at');
            }
            if (!Schema::hasColumn('chat_sessions', 'pending_close_at')) {
                $table->timestamp('pending_close_at')->nullable()->after('last_customer_message_at')->index();
            }
            if (!Schema::hasColumn('chat_sessions', 'pending_close_reason')) {
                $table->string('pending_close_reason', 100)->nullable()->after('pending_close_at');
            }
            if (!Schema::hasColumn('chat_sessions', 'waiting_customer_reply')) {
                $table->boolean('waiting_customer_reply')->default(false)->after('pending_close_reason')->index();
            }
        });

        if (Schema::hasColumn('chat_sessions', 'status') && DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE chat_sessions MODIFY status ENUM('active','handed_over','pending','assigned','ai','closed','expired') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('chat_sessions', 'status') && DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE chat_sessions MODIFY status ENUM('pending','assigned','closed') NOT NULL DEFAULT 'pending'");
        }

        Schema::table('chat_sessions', function (Blueprint $table) {
            $toDrop = [];
            foreach ([
                'closed_at',
                'last_staff_message_at',
                'last_customer_message_at',
                'pending_close_at',
                'pending_close_reason',
                'waiting_customer_reply',
            ] as $col) {
                if (Schema::hasColumn('chat_sessions', $col)) {
                    $toDrop[] = $col;
                }
            }
            if (!empty($toDrop)) {
                $table->dropColumn($toDrop);
            }
        });
    }
};

