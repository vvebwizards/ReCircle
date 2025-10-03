<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_blocking_fields_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('blocked_at')->nullable()->after('remember_token');
            $table->text('block_reason')->nullable()->after('blocked_at');
            $table->foreignId('blocked_by')->nullable()->after('block_reason')->constrained('users')->onDelete('set null');

            $table->index('blocked_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['blocked_by']);
            $table->dropColumn(['blocked_at', 'block_reason', 'blocked_by']);
        });
    }
};
