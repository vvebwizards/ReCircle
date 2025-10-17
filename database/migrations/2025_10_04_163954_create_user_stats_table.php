<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_user_stats_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('discussions_count')->default(0);
            $table->integer('replies_count')->default(0);
            $table->integer('likes_received')->default(0);
            $table->integer('solutions_provided')->default(0);
            $table->integer('days_active')->default(0);
            $table->integer('total_points')->default(0);
            $table->integer('current_streak')->default(0);
            $table->integer('longest_streak')->default(0);
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();

            $table->unique('user_id');
            $table->index('total_points'); // For leaderboards
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_stats');
    }
};
