<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CorreccionTareaNotificacion extends Notification
{
    use Queueable;
    public $details;
    public function __construct($details)
    {
        $this->details = $details;
    }
   
    public function via($notifiable)
    {
        return ['database'];
    }


    public function toArray($notifiable)
    {
        return $this->details;
    }
}
