<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('material_id')
                ->after('maker_id')
                ->nullable()
                ->constrained('materials')
                ->nullOnDelete();

            $table->json('dimensions')->after('material_passport')->nullable();
            $table->decimal('weight', 8, 2)->after('dimensions')->nullable();
            $table->text('care_instructions')->after('weight')->nullable();
            $table->integer('warranty_months')->after('care_instructions')->nullable();
            $table->boolean('is_featured')->after('warranty_months')->default(false);
            $table->json('tags')->after('is_featured')->nullable();
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

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['material_id']);

            $table->dropColumn([
                'material_id',
                'dimensions',
                'weight',
                'care_instructions',
                'warranty_months',
                'is_featured',
                'tags',
            ]);
        });
    }
};
