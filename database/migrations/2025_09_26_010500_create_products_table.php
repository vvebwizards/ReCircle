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
            $table->foreignId('work_order_id')
                ->nullable()
                ->constrained('work_orders')
                ->nullOnDelete();
            $table->foreignId('maker_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('material_id')
                ->nullable()
                ->constrained('materials')
                ->nullOnDelete();
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

        Schema::create('product_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();
            $table->foreignId('material_id')
                ->constrained('materials')
                ->cascadeOnDelete();
            $table->decimal('quantity_used', 10, 2);
            $table->string('unit', 10);
            $table->timestamps();

            $table->unique(['product_id', 'material_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_materials');
        Schema::dropIfExists('products');
    }
};
