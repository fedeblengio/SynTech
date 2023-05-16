<?php

namespace App\Http\Controllers;

use App\Models\usuarios;
use App\Models\Notifications;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function listarNotificaciones($idUsuario){
        $user = usuarios::find($idUsuario);
        $notificaciones = $user->getNotifications();
        return response()->json($notificaciones, 200);
    }
    public function marcarLeida($idNotificacion){
        $notificacion = Notifications::find($idNotificacion);
        $notificacion->update(['read_at' => now()]);
        return response()->json($notificacion, 200);
    }
}
