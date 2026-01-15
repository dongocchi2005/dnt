<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_sessions', 'conversion_type')) {
                $table->string('conversion_type', 40)->nullable()->after('status')->index();
            }
            if (!Schema::hasColumn('chat_sessions', 'converted_at')) {
                $table->timestamp('converted_at')->nullable()->after('conversion_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('chat_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('chat_sessions', 'converted_at')) {
                $table->dropColumn('converted_at');
            }
            if (Schema::hasColumn('chat_sessions', 'conversion_type')) {
                $table->dropColumn('conversion_type');
            }
        });
    }
};
