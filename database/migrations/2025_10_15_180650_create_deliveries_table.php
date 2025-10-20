<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();

            // Lien vers le pickup (obligatoire)
            $table->foreignId('pickup_id')
                ->constrained('pickups')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Le livreur qui prend en charge (user avec role "courier")
            $table->foreignId('courier_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Données côté livreur
            $table->string('courier_phone', 40)->nullable();

            // Hub fixe (par défaut). Tu peux changer la valeur par défaut.
            $table->string('hub_address')->default('ReCircle Hub — 12 Rue Exemple, Tunis');
            $table->decimal('hub_lat', 10, 7)->nullable();
            $table->decimal('hub_lng', 10, 7)->nullable();

            // Statut du delivery (différent de Pickup, orienté “transport”)
            $table->enum('status', [
                'scheduled',     // prêt à être pris en charge
                'assigned',      // assigné à un livreur
                'in_transit',    // en route vers le hub
                'delivered',     // arrivé au hub
                'failed',        // échec (panne, adresse introuvable…)
                'cancelled',     // annulé
            ])->default('scheduled');

            // Optionnel : suivi & timeline
            $table->string('tracking_code', 40)->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();   // moment de collecte chez le générateur
            $table->timestamp('arrived_hub_at')->nullable(); // moment d’arrivée au hub

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Unicité : 1 pickup -> 1 delivery (si tu veux forcer un seul delivery par pickup)
            $table->unique('pickup_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
