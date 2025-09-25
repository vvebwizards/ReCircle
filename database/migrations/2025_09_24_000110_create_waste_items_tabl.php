<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waste_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generator_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->json('images')->nullable();
            $table->decimal('estimated_weight', 10, 2)->nullable();
            $table->enum('condition', ['good', 'fixable', 'scrap'])->default('good');
            $table->json('location')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('generator_id');
            $table->index('condition');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waste_items');
    }
};
