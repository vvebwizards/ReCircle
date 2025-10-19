<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop if exists from any previous attempts
        Schema::dropIfExists('products');

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('maker_id');

            $table->string('sku')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('stock')->default(0);
            $table->string('status', 32)->default('draft');

            // Simple version - remove complex fields for now
            $table->timestamps();

            // Add foreign key separately
            $table->foreign('maker_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Add indexes
            $table->index('maker_id');
            $table->index('status');
            $table->index('sku');

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
