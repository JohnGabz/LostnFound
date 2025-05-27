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
        Schema::table('items', function (Blueprint $table) {
            // Change the status enum to include 'available' and remove 'pending', 'returned'
            $table->enum('status', ['available', 'claimed'])->default('available')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            // Revert back to original enum
            $table->enum('status', ['pending', 'claimed', 'returned'])->default('pending')->change();
        });
    }
};