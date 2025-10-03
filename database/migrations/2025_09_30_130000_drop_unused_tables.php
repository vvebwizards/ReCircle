<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables considered unused (no controllers referencing them currently)
     * Adjust if any are still needed.
     * Ordered by foreign key dependencies (child tables first)
     */
    private array $tables = [
        'impacts',
        'orders',
        'products',
        'process_steps',
        'pickups',
        'work_orders',
        'matches',
        'listings',
    ];

    public function up(): void
    {
        // Disable foreign key checks to allow dropping tables with dependencies
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::drop($table);
            }
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        // Down intentionally left blank; restoring these legacy/future tables requires original create migrations.
    }
};
