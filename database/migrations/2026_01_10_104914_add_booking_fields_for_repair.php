<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Change booking_date from date to datetime
            if (Schema::hasColumn('bookings', 'booking_date')) {
                $table->datetime('booking_date')->change();
            }

            // Add new fields for receive method and shipping
            if (!Schema::hasColumn('bookings', 'receive_method')) {
                $table->enum('receive_method', ['store', 'ship'])->default('store')->after('device_issue');
            }
            if (!Schema::hasColumn('bookings', 'shipping_provider')) {
                $table->string('shipping_provider')->nullable()->after('receive_method');
            }
            if (!Schema::hasColumn('bookings', 'pickup_address')) {
                $table->text('pickup_address')->nullable()->after('shipping_provider');
            }
            if (!Schema::hasColumn('bookings', 'notes')) {
                $table->text('notes')->nullable()->after('pickup_address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['receive_method', 'shipping_provider', 'pickup_address', 'notes']);
            $table->date('booking_date')->change();
        });
    }
};
