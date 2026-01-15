<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_phone', 30);
            $table->string('customer_address')->nullable();
            $table->string('receive_method', 20);
            $table->string('return_method', 20);
            $table->string('status', 30)->default('pending');
            $table->decimal('deposit_amount', 12, 2)->default(0);
            $table->decimal('inspection_fee', 12, 2)->default(0);
            $table->decimal('repair_fee', 12, 2)->default(0);
            $table->decimal('shipping_fee', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->boolean('is_fully_paid')->default(false);
            $table->text('notes_customer')->nullable();
            $table->text('notes_admin')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};
