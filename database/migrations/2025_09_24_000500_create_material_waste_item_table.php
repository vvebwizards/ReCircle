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
        Schema::create('material_waste_item', function (Blueprint $table) {
            $table->foreignId('material_id')->constrained('materials')->cascadeOnDelete();
            $table->foreignId('waste_item_id')->constrained('waste_items')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['material_id', 'waste_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_waste_item');
    }
};
