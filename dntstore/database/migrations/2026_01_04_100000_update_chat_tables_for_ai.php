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
            if (!Schema::hasColumn('chat_sessions', 'title')) {
                $table->string('title')->nullable()->after('session_id');
            }
            if (!Schema::hasColumn('chat_sessions', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            }
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_messages', 'role')) {
                $table->enum('role', ['user', 'assistant', 'system'])->nullable()->after('chat_session_id')->index();
            }
            if (!Schema::hasColumn('chat_messages', 'content')) {
                $table->longText('content')->nullable()->after('role');
            }
        });

        try {
            $rows = DB::table('chat_messages')->select('id', 'sender', 'message')->get();
            foreach ($rows as $r) {
                $role = $r->sender === 'user' ? 'user' : ($r->sender === 'admin' ? 'admin' : 'assistant');
                DB::table('chat_messages')->where('id', $r->id)->update([
                    'role' => $role,
                    'content' => $r->message,
                ]);
            }
        } catch (\Throwable $e) {
            // swallow to avoid failure if columns mismatch; admin can re-run later
        }
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            if (Schema::hasColumn('chat_messages', 'content')) {
                $table->dropColumn('content');
            }
            if (Schema::hasColumn('chat_messages', 'role')) {
                $table->dropColumn('role');
            }
        });

        Schema::table('chat_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('chat_sessions', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('chat_sessions', 'title')) {
                $table->dropColumn('title');
            }
        });
    }
};
