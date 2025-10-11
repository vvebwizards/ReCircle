<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materials', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->enum('category', ['wood', 'metal', 'plastic', 'textile', 'electronic', 'glass', 'paper']);
    $table->enum('unit', ['kg', 'pcs', 'm2', 'l']);
    $table->decimal('quantity', 10, 2)->default(0);
    $table->unsignedTinyInteger('recyclability_score')->default(0);

    $table->decimal('co2_kg_saved', 10, 2)->default(0);
    $table->decimal('landfill_kg_avoided', 10, 2)->default(0);
    $table->decimal('energy_saved_kwh', 10, 2)->default(0);

    $table->foreignId('maker_id')->nullable()->constrained('users')->nullOnDelete();
    $table->foreignId('waste_item_id')->nullable()->constrained('waste_items')->nullOnDelete();

    $table->text('description')->nullable();
    $table->timestamps();
    $table->softDeletes();
});

    }

    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
