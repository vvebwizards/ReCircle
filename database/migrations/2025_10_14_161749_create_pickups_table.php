<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pickups', function (Blueprint $t) {
            $t->id();

            $t->foreignId('waste_item_id')->nullable()
                ->constrained('waste_items')->nullOnDelete()->cascadeOnUpdate();

            $t->foreignId('courier_id')->nullable()
                ->constrained('users')->nullOnDelete()->cascadeOnUpdate();

            // Données métier
            $t->string('pickup_address', 255);
            $t->timestamp('scheduled_pickup_window_start')->nullable();
            $t->timestamp('scheduled_pickup_window_end')->nullable();
            $t->string('status', 32)->default('scheduled');
            $t->string('tracking_code', 64)->unique();
            $t->text('notes')->nullable();

            $t->timestamps(); // created_at / updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pickups');
    }
};
