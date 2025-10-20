<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_forum_votes_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->morphs('votable'); // Can vote on discussions or replies
            $table->enum('type', ['up', 'down']);
            $table->timestamps();

            $table->unique(['user_id', 'votable_id', 'votable_type']);
            $table->index(['votable_id', 'votable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_votes');
    }
};
