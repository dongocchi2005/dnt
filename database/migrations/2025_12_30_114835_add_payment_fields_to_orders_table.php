<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'user_id')) {
                $table->unsignedBigInteger('user_id')->after('id');
            }

            if (!Schema::hasColumn('orders', 'total_amount')) {
                $table->decimal('total_amount', 12, 2)->default(0)->after('user_id');
            }

            if (!Schema::hasColumn('orders', 'payment_status')) {
                $table->string('payment_status')->default('pending')->after('total_amount');
            }

            if (!Schema::hasColumn('orders', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('payment_status');
            }

            if (!Schema::hasColumn('orders', 'transaction_id')) {
                $table->string('transaction_id')->nullable()->after('payment_method');
            }
        });

        // Thêm foreign key (nếu bạn muốn)
        Schema::table('orders', function (Blueprint $table) {
            // tránh lỗi nếu bạn chạy lại
            // MySQL cần tên constraint, nhưng cách đơn giản là thử-catch bằng tay.
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'transaction_id')) $table->dropColumn('transaction_id');
            if (Schema::hasColumn('orders', 'payment_method')) $table->dropColumn('payment_method');
            if (Schema::hasColumn('orders', 'payment_status')) $table->dropColumn('payment_status');
            if (Schema::hasColumn('orders', 'total_amount')) $table->dropColumn('total_amount');
            if (Schema::hasColumn('orders', 'user_id')) $table->dropColumn('user_id');
        });
    }
};
