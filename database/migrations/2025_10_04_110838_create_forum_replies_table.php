<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_forum_replies_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_replies', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('discussion_id')->constrained('forum_discussions')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('forum_replies')->onDelete('cascade');
            $table->integer('depth')->default(0); // For nested replies
            $table->integer('like_count')->default(0);
            $table->boolean('is_answer')->default(false); // Mark as solution
            $table->timestamps();
            
            $table->index(['discussion_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_replies');
    }
};