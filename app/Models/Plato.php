<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plato extends Model
{
    protected $fillable = ['nombre', 'descripcion', 'precio', 'imagen', 'categoria_id', 'estado'];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }
}
