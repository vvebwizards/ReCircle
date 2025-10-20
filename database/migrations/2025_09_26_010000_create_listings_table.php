<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('waste_item_id')->constrained('waste_items')->cascadeOnDelete();
            $table->string('status', 32)->default('active')->index();
            $table->decimal('min_price', 10, 2)->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
            $table->unique('waste_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
