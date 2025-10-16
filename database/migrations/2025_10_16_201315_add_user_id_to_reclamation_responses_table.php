<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdToReclamationResponsesTable extends Migration
{
    public function up()
    {
        Schema::table('reclamation_responses', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');

            // Also make admin_id nullable since we'll use either admin_id OR user_id
            $table->foreignId('admin_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('reclamation_responses', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->foreignId('admin_id')->nullable(false)->change();
        });
    }
}
