<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PagoController extends Controller
{
    //
    public function store(Request $request)
{
    $data = $request->validate([
        'prestamo_id'  => ['required', 'exists:prestamos,id'],
        'tipo_pago'    => ['required', 'in:Interes,Capital'],
        'monto_pagado' => ['required', 'numeric','min:0.01'],
        'fecha_pago'   => ['required', 'date'],
    ]);

    // Crea el pago
    $pago = \App\Models\Pago::create($data);

    // (Opcional) Actualiza status del préstamo o saldo en caja, etc.

    return back()->with('success', 'Pago registrado.');
}

}
