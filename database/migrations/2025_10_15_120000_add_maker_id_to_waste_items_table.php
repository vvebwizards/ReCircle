<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('waste_items', function (Blueprint $table) {
            $table->foreignId('maker_id')->nullable()->after('generator_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('waste_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('maker_id');
        });
    }
};
