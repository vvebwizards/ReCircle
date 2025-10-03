<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['work_order_id']);

            $table->foreignId('work_order_id')
                ->nullable()
                ->change();

            $table->foreign('work_order_id')
                ->references('id')
                ->on('work_orders')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['work_order_id']);

            $table->foreignId('work_order_id')
                ->nullable(false)
                ->change();

            $table->foreign('work_order_id')
                ->references('id')
                ->on('work_orders')
                ->cascadeOnDelete();
        });
    }
};
