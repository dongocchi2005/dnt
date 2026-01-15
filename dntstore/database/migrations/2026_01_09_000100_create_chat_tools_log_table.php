<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_tools_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chat_session_id')->nullable()->index();
            $table->string('type', 80)->index();
            $table->string('status', 30)->default('success')->index();
            $table->json('input')->nullable();
            $table->json('output')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('chat_session_id')
                ->references('id')
                ->on('chat_sessions')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_tools_log');
    }
};
