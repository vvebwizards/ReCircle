<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maker_id')->constrained('users')->cascadeOnDelete();

            $table->string('sku')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('stock')->default(0);
            $table->string('status', 32)->default('draft')->index();

            $table->json('dimensions')->nullable();
            $table->json('material_passport')->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->text('care_instructions')->nullable();
            $table->integer('warranty_months')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->json('tags')->nullable();
            $table->timestamp('published_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
            Schema::dropIfExists('products');
    }
};
