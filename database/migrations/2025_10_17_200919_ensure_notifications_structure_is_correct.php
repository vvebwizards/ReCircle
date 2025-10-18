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
        // Vérifier si la colonne pickup_id existe déjà
        if (! Schema::hasColumn('notifications', 'pickup_id')) {
            Schema::table('notifications', function (Blueprint $table) {
                // Ajouter la colonne pickup_id pour lier directement au pickup
                $table->unsignedBigInteger('pickup_id')->nullable()->after('id');

                // Ajouter l'index pour améliorer les performances
                $table->index('pickup_id');
                $table->index(['notifiable_id', 'read_at']);
            });
        }

        // Vérifier et ajouter la contrainte de clé étrangère si elle n'existe pas
        Schema::table('notifications', function (Blueprint $table) {
            // Ajouter la contrainte de clé étrangère pour pickup_id
            if (Schema::hasColumn('notifications', 'pickup_id')) {
                try {
                    $table->foreign('pickup_id')->references('id')->on('pickups')->onDelete('cascade');
                } catch (Exception $e) {
                    // La contrainte existe déjà, ignorer l'erreur
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Supprimer la contrainte de clé étrangère
            $table->dropForeign(['pickup_id']);

            // Supprimer les index
            $table->dropIndex(['pickup_id']);
            $table->dropIndex(['notifiable_id', 'read_at']);

            // Supprimer la colonne pickup_id
            $table->dropColumn('pickup_id');
        });
    }
};
