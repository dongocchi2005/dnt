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
            $table->datetime('booking_date')->change();

            // Add new fields for receive method and shipping
            $table->enum('receive_method', ['store', 'ship'])->default('store')->after('device_issue');
            $table->string('shipping_provider')->nullable()->after('receive_method');
            $table->text('pickup_address')->nullable()->after('shipping_provider');
            $table->text('notes')->nullable()->after('pickup_address');
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
