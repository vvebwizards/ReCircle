<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waste_bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('waste_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('maker_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3); // USD, EUR, TND
            $table->text('notes')->nullable();
            $table->string('status', 16)->default('pending')->index(); // pending, accepted, rejected, withdrawn
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('withdrawn_at')->nullable();
            $table->timestamps();

            $table->index(['waste_item_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waste_bids');
    }
};
