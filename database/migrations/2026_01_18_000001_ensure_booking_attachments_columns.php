<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('booking_attachments')) {
            return;
        }

        Schema::table('booking_attachments', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_attachments', 'booking_id')) {
                $table->unsignedBigInteger('booking_id')->nullable()->after('id');
                $table->index(['booking_id']);
            }
            if (!Schema::hasColumn('booking_attachments', 'path')) {
                $table->string('path')->nullable();
            }
            if (!Schema::hasColumn('booking_attachments', 'original_name')) {
                $table->string('original_name')->nullable();
            }
            if (!Schema::hasColumn('booking_attachments', 'mime')) {
                $table->string('mime')->nullable();
            }
            if (!Schema::hasColumn('booking_attachments', 'size')) {
                $table->unsignedBigInteger('size')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('booking_attachments')) {
            return;
        }

        Schema::table('booking_attachments', function (Blueprint $table) {
            $cols = [];
            foreach (['size', 'mime', 'original_name', 'path', 'booking_id'] as $col) {
                if (Schema::hasColumn('booking_attachments', $col)) {
                    $cols[] = $col;
                }
            }
            if ($cols !== []) {
                $table->dropColumn($cols);
            }
        });
    }
};

