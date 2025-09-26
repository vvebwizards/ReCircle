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
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignId('maker_id')->constrained('users')->cascadeOnDelete();
            $table->string('sku')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('images')->nullable();
            $table->json('material_passport')->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->decimal('price', 10, 2);
            $table->string('status', 32)->default('draft')->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique('work_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
