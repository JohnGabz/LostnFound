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
        Schema::create('items', function (Blueprint $table) {
            $table->id('item_id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->enum('type', ['lost', 'found']);
            $table->string('title', 100);
            $table->text('description')->nullable();
            $table->string('category', 100);
            $table->date('date_lost_found');
            $table->string('location', 100);
            $table->string('image_path', 255)->nullable();
            $table->enum('status', ['pending', 'claimed', 'returned'])->default('pending');
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
