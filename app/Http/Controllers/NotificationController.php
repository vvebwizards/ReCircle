<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Récupérer les notifications non lues pour l'utilisateur connecté
     */
    public function getUnreadNotifications(): JsonResponse
    {
        $user = auth()->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié',
                'notifications' => [],
                'count' => 0,
            ], 401);
        }

        $notifications = NotificationService::getUnreadNotifications($user);

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'count' => $notifications->count(),
        ]);
    }

    /**
     * Récupérer TOUTES les notifications (lues et non lues) pour l'utilisateur connecté
     */
    public function getAllNotifications(): JsonResponse
    {
        $user = auth()->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié',
                'notifications' => [],
                'count' => 0,
            ], 401);
        }

        $notifications = NotificationService::getAllNotifications($user);

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'count' => $notifications->count(),
        ]);
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(Request $request): JsonResponse
    {
        $request->validate([
            'notification_id' => 'required|string|exists:notifications,id',
        ]);

        $success = NotificationService::markAsRead($request->notification_id);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Notification marquée comme lue' : 'Erreur lors de la mise à jour',
        ]);
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead(): JsonResponse
    {
        $user = auth()->user();
        $count = NotificationService::markAllAsRead($user);

        return response()->json([
            'success' => true,
            'message' => "{$count} notifications marquées comme lues",
        ]);
    }

    /**
     * Supprimer une notification
     */
    public function delete(Request $request): JsonResponse
    {
        $request->validate([
            'notification_id' => 'required|string|exists:notifications,id',
        ]);

        $success = NotificationService::deleteNotification($request->notification_id);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Notification supprimée' : 'Erreur lors de la suppression',
        ]);
    }

    /**
     * Récupérer le nombre de notifications non lues
     */
    public function getUnreadCount(): JsonResponse
    {
        $user = auth()->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié',
                'count' => 0,
            ], 401);
        }

        $count = \Illuminate\Notifications\DatabaseNotification::where('notifiable_id', $user->id)
            ->where('notifiable_type', \App\Models\User::class)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }
}
