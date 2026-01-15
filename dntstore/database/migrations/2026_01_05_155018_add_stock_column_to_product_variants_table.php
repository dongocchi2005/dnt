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
        // No-op: stock column already added by previous migration
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: stock column should not be dropped as it's already in use
    }
};
