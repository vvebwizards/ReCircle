<?php
// database/migrations/2024_01_01_000000_create_follows_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('following_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['follower_id', 'following_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follows');
    }
};