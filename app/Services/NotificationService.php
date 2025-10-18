<?php

namespace App\Services;

use App\Models\Pickup;
use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

class NotificationService
{
    /**
     * Envoyer une notification à tous les admins quand un pickup est créé
     */
    public static function notifyAdminsPickupCreated(Pickup $pickup): void
    {
        // Récupérer tous les utilisateurs admin
        $admins = User::where('role', 'admin')->get();

        // Récupérer le générateur (user_id du pickup)
        $generator = $pickup->wasteItem->generator;

        foreach ($admins as $admin) {
            $notificationId = Str::uuid();
            DatabaseNotification::create([
                'id' => $notificationId,
                'pickup_id' => $pickup->id, // ID du pickup directement dans la table
                'notifiable_id' => $admin->id, // Admin qui reçoit
                'notifiable_type' => User::class,
                'type' => 'App\Notifications\PickupCreatedNotification',
                'data' => [
                    'user_id' => $generator->id, // Générateur qui a créé le pickup
                    'title' => 'Nouveau Pickup Créé',
                    'message' => "The generator {$generator->name} has created a new pickup for waste item: {$pickup->wasteItem->title} with notification id: {$notificationId}",
                    'generator_name' => $generator->name,
                    'generator_email' => $generator->email,
                    'waste_item' => $pickup->wasteItem->title,
                    'pickup_address' => $pickup->pickup_address,
                    'pickup_tracking_code' => $pickup->tracking_code,
                ],
                'read_at' => null,
            ]);
        }
    }

    /**
     * Récupérer les notifications non lues pour un utilisateur
     */
    public static function getUnreadNotifications(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return DatabaseNotification::where('notifiable_id', $user->id)
            ->where('notifiable_type', User::class)
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Récupérer TOUTES les notifications (lues et non lues) pour un utilisateur
     */
    public static function getAllNotifications(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return DatabaseNotification::where('notifiable_id', $user->id)
            ->where('notifiable_type', User::class)
            ->orderBy('created_at', 'desc') // Plus récent vers plus ancien
            ->orderBy('id', 'desc') // Tri secondaire par ID pour garantir l'ordre
            ->get();
    }

    /**
     * Marquer une notification comme lue
     */
    public static function markAsRead(string $notificationId): bool
    {
        $notification = DatabaseNotification::find($notificationId);

        if ($notification) {
            $notification->markAsRead();

            return true;
        }

        return false;
    }

    /**
     * Marquer toutes les notifications comme lues pour un utilisateur
     */
    public static function markAllAsRead(User $user): int
    {
        return DatabaseNotification::where('notifiable_id', $user->id)
            ->where('notifiable_type', User::class)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Supprimer une notification
     */
    public static function deleteNotification(string $notificationId): bool
    {
        $notification = DatabaseNotification::find($notificationId);

        if ($notification) {
            $notification->delete();

            return true;
        }

        return false;
    }
}
