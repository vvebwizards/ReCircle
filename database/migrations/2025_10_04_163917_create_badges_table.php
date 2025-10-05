<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_badges_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->string('icon'); // Font Awesome icon class
            $table->string('color')->default('#3B82F6'); // Badge color
            $table->enum('type', ['bronze', 'silver', 'gold', 'platinum'])->default('bronze');
            $table->string('criteria'); // What triggers this badge
            $table->integer('threshold')->default(1); // Number required to earn
            $table->boolean('is_active')->default(true);
            $table->integer('points')->default(0); // Points awarded
            $table->timestamps();

            $table->index(['type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
