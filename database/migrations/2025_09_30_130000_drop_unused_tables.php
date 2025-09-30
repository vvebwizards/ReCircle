<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables considered unused (no controllers referencing them currently)
     * Adjust if any are still needed.
     */
    private array $tables = [
        'impacts',
        'listings',
        'matches',
        'pickups',
        'work_orders',
        'process_steps',
        'products',
        'orders',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::drop($table);
            }
        }
    }

    public function down(): void
    {
        // Down intentionally left blank; restoring these legacy/future tables requires original create migrations.
    }
};
