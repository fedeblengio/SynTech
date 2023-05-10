<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
class usuarios extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Notifiable;

    protected $fillable = [
        'nombre',
        'email',
        'genero',
    ];

    protected $guarded = ['id','ou'];

    
    public function getNotifications($all = false)
    {
        if ($all) {
            return $this->notifications;
        }
        return $this->unreadNotifications->where('deleted_at', null);
    }


}
