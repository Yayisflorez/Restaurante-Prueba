<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reserva;
use Illuminate\Support\Str;

class ReservaController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'hora' => 'required',
            'personas' => 'required|integer',
            'zona' => 'required|string',
            'mesa' => 'required|string',
            'notas' => 'nullable|string'
        ]);

        $reserva = new Reserva($request->all());
        $reserva->user_id = auth()->id() ?? 1;
        $reserva->codigo_referencia = strtoupper(Str::random(8));
        $reserva->estado = 'pendiente';
        $reserva->save();

        return response()->json([
            'success' => true,
            'reserva' => $reserva
        ]);
    }
}
