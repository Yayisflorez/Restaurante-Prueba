<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    protected $fillable = [
        'user_id',
        'fecha',
        'hora',
        'personas',
        'zona',
        'notas',
        'estado',
        'mesa',
        'codigo_referencia',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
