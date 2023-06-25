<?php

namespace App\Jobs;
use Illuminate\Support\Facades\App;
use App\Notifications\NuevaTareaNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ProfesorCreaTareaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $details;
    public $alumno;
    public function __construct($details,$alumno)
    {
        $this->details = $details;
        $this->alumno = $alumno;
    }

  
    public function handle()
    {
        if(App::environment(['testing'])){
           return;
        }
        $alumnoEmail = $this->alumno->email;
        // Send the email
        Mail::send('emails.nueva_tarea', ['details' => $this->details], function ($message) use ($alumnoEmail) {
            $message->to($alumnoEmail)
                ->subject('Nueva tarea registrada');
        });
    }
}
