<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_sessions', 'guest_name')) {
                $table->string('guest_name')->nullable()->after('session_id');
            }
            if (!Schema::hasColumn('chat_sessions', 'guest_email')) {
                $table->string('guest_email')->nullable()->after('guest_name');
            }
            if (!Schema::hasColumn('chat_sessions', 'guest_phone')) {
                $table->string('guest_phone')->nullable()->after('guest_email');
            }

            if (!Schema::hasColumn('chat_sessions', 'assigned_admin_id')) {
                $table->unsignedBigInteger('assigned_admin_id')->nullable()->after('user_id');
                $table->foreign('assigned_admin_id')->references('id')->on('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('chat_sessions', 'assigned_at')) {
                $table->timestamp('assigned_at')->nullable()->after('assigned_admin_id');
            }
            if (!Schema::hasColumn('chat_sessions', 'last_message_at')) {
                $table->timestamp('last_message_at')->nullable()->after('assigned_at')->index();
            }
            if (!Schema::hasColumn('chat_sessions', 'last_handled_by')) {
                $table->enum('last_handled_by', ['bot', 'admin'])->default('bot')->after('last_message_at')->index();
            }
        });

        if (Schema::hasColumn('chat_sessions', 'user_name') && Schema::hasColumn('chat_sessions', 'guest_name')) {
            DB::table('chat_sessions')
                ->whereNull('guest_name')
                ->update(['guest_name' => DB::raw('user_name')]);
        }
        if (Schema::hasColumn('chat_sessions', 'user_email') && Schema::hasColumn('chat_sessions', 'guest_email')) {
            DB::table('chat_sessions')
                ->whereNull('guest_email')
                ->update(['guest_email' => DB::raw('user_email')]);
        }
        if (Schema::hasColumn('chat_sessions', 'user_phone') && Schema::hasColumn('chat_sessions', 'guest_phone')) {
            DB::table('chat_sessions')
                ->whereNull('guest_phone')
                ->update(['guest_phone' => DB::raw('user_phone')]);
        }
        if (Schema::hasColumn('chat_sessions', 'last_activity') && Schema::hasColumn('chat_sessions', 'last_message_at')) {
            DB::table('chat_sessions')
                ->whereNull('last_message_at')
                ->update(['last_message_at' => DB::raw('last_activity')]);
        }

        if (Schema::hasColumn('chat_sessions', 'status')) {
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE chat_sessions MODIFY status ENUM('active','handed_over','pending','assigned','closed') NOT NULL DEFAULT 'active'");
            }

            DB::table('chat_sessions')
                ->whereIn('status', ['active', 'handed_over'])
                ->update(['status' => 'pending']);

            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE chat_sessions MODIFY status ENUM('pending','assigned','closed') NOT NULL DEFAULT 'pending'");
            }
        }
    }

    public function down(): void
    {
        Schema::table('chat_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('chat_sessions', 'assigned_admin_id')) {
                $table->dropForeign(['assigned_admin_id']);
                $table->dropColumn('assigned_admin_id');
            }
            if (Schema::hasColumn('chat_sessions', 'assigned_at')) {
                $table->dropColumn('assigned_at');
            }
            if (Schema::hasColumn('chat_sessions', 'last_message_at')) {
                $table->dropColumn('last_message_at');
            }
            if (Schema::hasColumn('chat_sessions', 'last_handled_by')) {
                $table->dropColumn('last_handled_by');
            }
            if (Schema::hasColumn('chat_sessions', 'guest_name')) {
                $table->dropColumn('guest_name');
            }
            if (Schema::hasColumn('chat_sessions', 'guest_email')) {
                $table->dropColumn('guest_email');
            }
            if (Schema::hasColumn('chat_sessions', 'guest_phone')) {
                $table->dropColumn('guest_phone');
            }
        });
    }
};
