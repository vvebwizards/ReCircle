<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pickups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->foreignId('courier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('scheduled_pickup_window_start')->nullable();
            $table->timestamp('scheduled_pickup_window_end')->nullable();
            $table->string('status', 32)->default('scheduled')->index();
            $table->string('tracking_code', 64)->nullable()->unique();
            $table->timestamp('picked_up_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique('match_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pickups');
    }
};
