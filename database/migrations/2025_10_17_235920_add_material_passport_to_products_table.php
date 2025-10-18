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
        Schema::table('products', function (Blueprint $table) {
            // Add material_passport column
            if (! Schema::hasColumn('products', 'material_passport')) {
                $table->json('material_passport')->nullable()->after('description');
            }

            // Add other potentially missing columns used in material passport
            if (! Schema::hasColumn('products', 'care_instructions')) {
                $table->text('care_instructions')->nullable()->after('material_passport');
            }

            if (! Schema::hasColumn('products', 'warranty_months')) {
                $table->integer('warranty_months')->nullable()->after('care_instructions');
            }
        });

        // Also check if materials table has the required environmental impact columns
        Schema::table('materials', function (Blueprint $table) {
            if (! Schema::hasColumn('materials', 'co2_kg_saved')) {
                $table->decimal('co2_kg_saved', 10, 2)->default(0)->after('recyclability_score');
            }

            if (! Schema::hasColumn('materials', 'landfill_kg_avoided')) {
                $table->decimal('landfill_kg_avoided', 10, 2)->default(0)->after('co2_kg_saved');
            }

            if (! Schema::hasColumn('materials', 'energy_saved_kwh')) {
                $table->decimal('energy_saved_kwh', 10, 2)->default(0)->after('landfill_kg_avoided');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['material_passport', 'care_instructions', 'warranty_months']);
        });

        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn(['co2_kg_saved', 'landfill_kg_avoided', 'energy_saved_kwh']);
        });
    }
};
