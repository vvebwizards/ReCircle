<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_audit_logs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade'); // Admin who performed action
            $table->string('action'); // Action type: user_blocked, role_changed, etc.
            $table->text('description'); // Human readable description
            $table->string('ip_address', 45)->nullable(); // IP where action was performed from
            $table->json('metadata')->nullable(); // Additional data like old_role, new_role, etc.
            $table->timestamps();

            // Indexes for better performance
            $table->index(['action', 'created_at']);
            $table->index('admin_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
