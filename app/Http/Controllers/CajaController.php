<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CajaController extends Controller
{
    public function index(Request $request)
    {
        // Filtro de rango (Por defecto 'mes' para no cargar todo)
        $rango = $request->input('rango', 'mes'); 
        
        $query = Caja::query();

        // Aplicar filtros de fecha
        if ($rango === 'hoy') {
            $query->whereDate('fecha', Carbon::today());
        } elseif ($rango === 'semana') {
            $query->whereBetween('fecha', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($rango === 'mes') {
            $query->whereMonth('fecha', Carbon::now()->month)
                  ->whereYear('fecha', Carbon::now()->year);
        }
        // 'todo' no aplica filtro

        // Ordenar: lo más reciente primero
        $movimientos = $query->latest('fecha')->latest('id')
            ->paginate(15)
            ->appends(['rango' => $rango]);

        // DATOS PARA TARJETAS:
        // 1. Saldo REAL Global (Independiente del filtro)
        $saldoActual = Caja::latest('id')->value('saldo_caja') ?? 0;

        // 2. Totales DEL PERIODO FILTRADO (Para saber cuánto ganaste/gastaste en este día/mes)
        // Clonamos el query base (que ya tiene el filtro de fecha) para sumar
        $ingresosPeriodo = (clone $query)->where('tipo_movimiento', 'Ingreso')->sum('monto');
        $egresosPeriodo  = (clone $query)->where('tipo_movimiento', 'Egreso')->sum('monto');

        return Inertia::render('Caja/Index', [
            'movimientos' => $movimientos,
            'filtros' => ['rango' => $rango],
            'balance' => [
                'total_real' => $saldoActual,
                'ingresos_periodo' => $ingresosPeriodo,
                'egresos_periodo' => $egresosPeriodo,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo'        => 'required|in:Gasto,Capital',
            'descripcion' => 'required|string|max:255',
            'monto'       => 'required|numeric|min:0.1',
            'fecha'       => 'required|date', // Validamos que envíen fecha
        ]);

        DB::transaction(function () use ($request) {
            // Nota: Si insertas una fecha antigua, el saldo histórico no se recalcula hacia adelante 
            // en este sistema simple (solo afecta el saldo final acumulado). 
            // Para sistemas contables estrictos se requiere un recálculo masivo.
            // Aquí asumimos que el saldo se ajusta al "ahora".
            
            $ultimoSaldo = Caja::latest('id')->value('saldo_caja') ?? 0;
            
            if ($request->tipo === 'Capital') {
                $tipoMovimiento = 'Ingreso';
                $origen = 'Aporte';
                $nuevoSaldo = $ultimoSaldo + $request->monto;
            } else { // Gasto
                $tipoMovimiento = 'Egreso';
                $origen = 'Gasto';
                $nuevoSaldo = $ultimoSaldo - $request->monto;
            }

            Caja::create([
                'tipo_movimiento' => $tipoMovimiento,
                'origen'          => $origen,
                'descripcion'     => $request->descripcion,
                'monto'           => $request->monto,
                'saldo_caja'      => $nuevoSaldo,
                'fecha'           => $request->fecha, // Usamos la fecha elegida
            ]);
        });

        return back()->with('success', 'Movimiento registrado correctamente.');
    }
}