<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\NotificationActivite;


class NotificationActiviteController extends Controller
{
    public function getNotificationsNonLues()
{
    // Récupérer l'utilisateur connecté
    $user = Auth::user();

    if (!$user) {
        return response()->json(['error' => 'Utilisateur non connecté'], 401);
    }

    // Récupérer toutes les notifications non lues de l'utilisateur
    $notificationsNonLues = NotificationActivite::where('user_id', $user->id)
        ->where('lu', 0) // Notifications non lues
        ->get();

    // Retourner les notifications
    return response()->json([
        'notifications' => $notificationsNonLues,
        'notificationsCount' => $notificationsNonLues->count(), // Nombre de notifications non lues
    ]);
}
public function marquerCommeLue($notificationId)
{
    $notification = NotificationActivite::find($notificationId);

    if (!$notification) {
        return response()->json(['error' => 'Notification non trouvée'], 404);
    }

    // Marquer la notification comme lue
    $notification->lu = 1;
    $notification->save();

    return response()->json(['message' => 'Notification marquée comme lue']);
}

}
