<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Prestamo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagoController extends Controller
{
    public function store(Request $request)
    {
        // Tu lógica actual de store...
        // Solo asegúrate de validar bien los datos
        $validated = $request->validate([
            'prestamo_id' => 'required|exists:prestamos,id',
            'monto' => 'required|numeric|min:1',
            'tipo' => 'required|in:Interes,Capital',
            'fecha_pago' => 'required|date',
        ]);

        Pago::create([
            'prestamo_id' => $validated['prestamo_id'],
            'tipo_pago' => $validated['tipo'],
            'monto_pagado' => $validated['monto'],
            'fecha_pago' => $validated['fecha_pago'],
        ]);

        return back()->with('success', 'Pago registrado correctamente.');
    }

    /**
     * NUEVO: Editar un pago existente
     */
    public function update(Request $request, Pago $pago)
    {
        $validated = $request->validate([
            'monto_pagado' => 'required|numeric|min:0.1',
            'fecha_pago' => 'required|date',
            'tipo_pago' => 'required|in:Interes,Capital'
        ]);

        $pago->update($validated);

        return back()->with('success', 'Pago corregido exitosamente.');
    }

    /**
     * NUEVO: Eliminar un pago (si hubo error grave)
     */
    public function destroy(Pago $pago)
    {
        $pago->delete();
        return back()->with('success', 'Pago eliminado del historial.');
    }
}