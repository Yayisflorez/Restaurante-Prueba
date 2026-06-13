<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetallePedido extends Model
{
    protected $fillable = ['pedido_id', 'plato_id', 'cantidad', 'precio_unitario', 'subtotal'];

    public function plato()
    {
        return $this->belongsTo(Plato::class);
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }
}
