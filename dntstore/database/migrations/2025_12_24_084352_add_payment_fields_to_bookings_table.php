<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            Schema::table('bookings', function (Blueprint $table) {
                if (!Schema::hasColumn('bookings', 'payment_status')) {
                    $table->string('payment_status')->default('pending')->after('price');
                }

                if (!Schema::hasColumn('bookings', 'payment_method')) {
                    $table->string('payment_method')->nullable()->after('payment_status');
                }

                if (!Schema::hasColumn('bookings', 'transaction_id')) {
                    $table->string('transaction_id')->nullable()->after('payment_method');
                }
            });
        } catch (QueryException $e) {
            if (($e->errorInfo[0] ?? null) === '42S21' || str_contains($e->getMessage(), 'Duplicate column name')) {
                return;
            }

            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('bookings', 'payment_status')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('payment_status');
            });
        }

        if (Schema::hasColumn('bookings', 'payment_method')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('payment_method');
            });
        }

        if (Schema::hasColumn('bookings', 'transaction_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('transaction_id');
            });
        }
    }
};
