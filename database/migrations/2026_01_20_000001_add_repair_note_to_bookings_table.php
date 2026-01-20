<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::table('bookings', function (Blueprint $table) {
                if (!Schema::hasColumn('bookings', 'repair_note')) {
                    $table->text('repair_note')->nullable()->after('device_issue');
                }
            });
        } catch (QueryException $e) {
            if (($e->errorInfo[0] ?? null) === '42S21' || str_contains($e->getMessage(), 'Duplicate column')) {
                return;
            }
            throw $e;
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('bookings', 'repair_note')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('repair_note');
            });
        }
    }
};
