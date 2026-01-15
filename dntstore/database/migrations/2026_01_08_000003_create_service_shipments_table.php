<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_order_id')->constrained()->cascadeOnDelete();
            $table->string('direction', 20);
            $table->string('carrier', 20);
            $table->string('tracking_code')->nullable();
            $table->string('label_url')->nullable();
            $table->decimal('fee', 12, 2)->default(0);
            $table->decimal('cod_amount', 12, 2)->default(0);
            $table->string('status', 20)->default('created');
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_shipments');
    }
};
