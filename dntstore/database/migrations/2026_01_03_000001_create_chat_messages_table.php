<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('chat_session_id');
            $table->enum('sender', ['user', 'bot', 'admin'])->index();
            $table->text('message');

            $table->timestamps();

            $table->foreign('chat_session_id')
                ->references('id')
                ->on('chat_sessions')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
