<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impacts', function (Blueprint $table) {
            $table->id();
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');
            $table->decimal('co2_kg_saved', 10, 3)->nullable();
            $table->decimal('landfill_kg_avoided', 10, 3)->nullable();
            $table->decimal('distance_km', 10, 2)->nullable();
            $table->timestamp('computed_at');
            $table->json('calc_details')->nullable();
            $table->timestamps();
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impacts');
    }
};
