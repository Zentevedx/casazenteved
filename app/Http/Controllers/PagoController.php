<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Prestamo;
use App\Models\Caja; // <--- IMPORTANTE: Modelo Caja importado
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagoController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'prestamo_id' => 'required|exists:prestamos,id',
            'monto' => 'required|numeric|min:1',
            'tipo' => 'required|in:Interes,Capital',
            'fecha_pago' => 'required|date',
        ]);

        DB::transaction(function () use ($validated) {
            // 1. Registrar el Pago en el historial del préstamo
            $pago = Pago::create([
                'prestamo_id' => $validated['prestamo_id'],
                'tipo_pago' => $validated['tipo'],
                'monto_pagado' => $validated['monto'],
                'fecha_pago' => $validated['fecha_pago'],
            ]);

            // 2. AUTOMATIZACIÓN CAJA: Registrar Entrada (Ingreso)
            $ultimoSaldo = Caja::latest('id')->value('saldo_caja') ?? 0;

            Caja::create([
                'tipo_movimiento' => 'Ingreso',
                'origen'          => 'Pago',
                'descripcion'     => "Pago de {$validated['tipo']} (Préstamo #{$validated['prestamo_id']})",
                'monto'           => $validated['monto'],
                'saldo_caja'      => $ultimoSaldo + $validated['monto'], // Sumamos el dinero
                'fecha'           => $validated['fecha_pago'],
                'referencia_id'   => $pago->id,
                'referencia_tabla'=> 'pagos',
            ]);
        });

        return back()->with('success', 'Pago registrado e ingresado a caja.');
    }

    public function update(Request $request, Pago $pago)
    {
        $validated = $request->validate([
            'monto_pagado' => 'required|numeric|min:0.1',
            'fecha_pago' => 'required|date',
            'tipo_pago' => 'required|in:Interes,Capital'
        ]);

        // Nota: Al igual que en préstamos, editar un pago histórico no recalcula 
        // automáticamente toda la caja hacia adelante en esta versión simple.
        $pago->update($validated);

        return back()->with('success', 'Pago corregido exitosamente.');
    }

    public function destroy(Pago $pago)
    {
        $pago->delete();
        return back()->with('success', 'Pago eliminado del historial.');
    }
}