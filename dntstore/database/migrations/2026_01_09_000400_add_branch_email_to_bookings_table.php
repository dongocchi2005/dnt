<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'customer_email')) {
                $table->string('customer_email')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('bookings', 'branch')) {
                $table->string('branch')->nullable()->after('customer_email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'branch')) {
                $table->dropColumn('branch');
            }
            if (Schema::hasColumn('bookings', 'customer_email')) {
                $table->dropColumn('customer_email');
            }
        });
    }
};
