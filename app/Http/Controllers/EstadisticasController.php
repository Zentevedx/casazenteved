<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Pago;
use App\Models\Prestamo;
use App\Models\Caja;
use App\Models\Articulo;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EstadisticasController extends Controller
{
    public function index(Request $request)
    {
        $modo = $request->input('modo', 'mes'); 
        $fecha = $request->input('fecha', now()->toDateString());

        // 1. Definición de Rangos de Tiempo (Igual que antes)
        switch ($modo) {
            case 'dia':
                $from = Carbon::parse($fecha)->startOfDay();
                $to = Carbon::parse($fecha)->endOfDay();
                $subDate = $from->copy()->subDay();
                break;
            case 'semana':
                $from = Carbon::parse($fecha)->startOfWeek();
                $to = Carbon::parse($fecha)->endOfWeek();
                $subDate = $from->copy()->subWeek();
                break;
            case 'mes':
                $from = Carbon::parse($fecha)->startOfMonth();
                $to = Carbon::parse($fecha)->endOfMonth();
                $subDate = $from->copy()->subMonth();
                break;
            default:
                $from = Carbon::now()->startOfMonth();
                $to = Carbon::now()->endOfMonth();
                $subDate = $from->copy()->subMonth();
        }

        $prevFrom = $subDate->copy()->startOf($modo === 'dia' ? 'day' : ($modo === 'semana' ? 'week' : 'month'));
        $prevTo = $subDate->copy()->endOf($modo === 'dia' ? 'day' : ($modo === 'semana' ? 'week' : 'month'));

        // --- KPIS FINANCIEROS (El motor del negocio) ---
        $colocadoActual = Prestamo::whereBetween('fecha_prestamo', [$from, $to])->sum('monto');
        $colocadoPrevio = Prestamo::whereBetween('fecha_prestamo', [$prevFrom, $prevTo])->sum('monto');
        
        $ingresosActual = Pago::whereBetween('fecha_pago', [$from, $to])->where('tipo_pago', 'Interes')->sum('monto_pagado');
        $ingresosPrevio = Pago::whereBetween('fecha_pago', [$prevFrom, $prevTo])->where('tipo_pago', 'Interes')->sum('monto_pagado');

        // --- INTELIGENCIA DE INVENTARIO (Toma de decisiones sobre prendas) ---
        // Valor de las prendas que tenemos guardadas (De préstamos Activos o Vencidos)
        $valorPrendasCustodia = Prestamo::whereIn('estado', ['Activo', 'Vencido'])->sum('monto');
        $totalArticulosCustodia = Articulo::whereHas('prestamo', function($q) {
            $q->whereIn('estado', ['Activo', 'Vencido']);
        })->count();

        // --- INTELIGENCIA DE CLIENTES (CRM) ---
        // 1. Top Clientes VIP: Los que más intereses han pagado históricamente (Cuidarlos)
        $topClientesVIP = DB::table('clientes')
            ->join('prestamos', 'clientes.id', '=', 'prestamos.cliente_id')
            ->join('pagos', 'prestamos.id', '=', 'pagos.prestamo_id')
            ->where('pagos.tipo_pago', 'Interes')
            ->select('clientes.nombre', DB::raw('SUM(pagos.monto_pagado) as total_aportado'), DB::raw('COUNT(DISTINCT prestamos.id) as num_prestamos'))
            ->groupBy('clientes.id', 'clientes.nombre')
            ->orderByDesc('total_aportado')
            ->limit(5)
            ->get();

        // 2. Top Riesgo: Clientes con mayor deuda activa acumulada (Vigilarlos)
        $topClientesRiesgo = Prestamo::whereIn('estado', ['Activo', 'Vencido'])
            ->select('cliente_id', DB::raw('SUM(monto) as deuda_total'), DB::raw('COUNT(id) as prestamos_activos'))
            ->with('cliente:id,nombre') // Eager loading eficiente
            ->groupBy('cliente_id')
            ->orderByDesc('deuda_total')
            ->limit(5)
            ->get()
            ->map(fn($p) => [
                'nombre' => $p->cliente->nombre ?? 'Desconocido',
                'deuda' => $p->deuda_total,
                'cantidad' => $p->prestamos_activos
            ]);

        // Estructura de KPIs Globales
        $kpis = [
            'colocado' => [
                'valor' => $colocadoActual,
                'variacion' => $this->calcularVariacion($colocadoActual, $colocadoPrevio)
            ],
            'ingresos' => [
                'valor' => $ingresosActual,
                'variacion' => $this->calcularVariacion($ingresosActual, $ingresosPrevio)
            ],
            'inventario' => [
                'valor_total' => $valorPrendasCustodia,
                'items' => $totalArticulosCustodia
            ],
            'operaciones' => Prestamo::whereBetween('fecha_prestamo', [$from, $to])->count()
        ];

        // --- GRÁFICOS (Tendencias) ---
        $fechas = collect();
        $flujoNeto = collect();
        $period = new \DatePeriod($from, \DateInterval::createFromDateString('1 day'), $to->copy()->addDay());

        foreach ($period as $date) {
            $fechas->push($date->format('d M'));
            $entrada = Pago::whereDate('fecha_pago', $date)->sum('monto_pagado');
            $salida = Prestamo::whereDate('fecha_prestamo', $date)->sum('monto') 
                    + Caja::where('tipo_movimiento', 'Egreso')->where('origen', 'Gasto')->whereDate('fecha', $date)->sum('monto');
            $flujoNeto->push($entrada - $salida);
        }

        return Inertia::render('Estadisticas', [
            'kpis' => $kpis,
            'ranking' => [
                'vip' => $topClientesVIP,
                'riesgo' => $topClientesRiesgo
            ],
            'graficos' => [
                'fechas' => $fechas,
                'flujo_neto' => $flujoNeto,
            ],
            'reporteCaja' => $this->generarReporteDetallado($from, $to), // (Tu función original)
            'filtros' => [
                'modo' => $modo,
                'fecha' => $fecha,
                'rango_texto' => $from->translatedFormat('d M') . ' - ' . $to->translatedFormat('d M Y')
            ]
        ]);
    }

    // Funciones auxiliares (sin cambios significativos)
    private function calcularVariacion($actual, $previo) {
        if ($previo == 0) return $actual > 0 ? 100 : 0;
        return (($actual - $previo) / $previo) * 100;
    }

    private function generarReporteDetallado($from, $to) {
        // ... (Mantén aquí tu lógica original de la tabla de caja)
        return [
             'capital_saliente' => Prestamo::whereBetween('fecha_prestamo', [$from, $to])->get()->map(fn($p) => ['codigo' => $p->codigo, 'monto' => $p->monto, 'fecha' => $p->fecha_prestamo, 'detalle' => "Cliente #{$p->cliente_id}"]),
            'capital_entrante' => Pago::where('tipo_pago', 'Capital')->whereBetween('fecha_pago', [$from, $to])->with('prestamo')->get()->map(fn($p) => ['codigo' => $p->prestamo->codigo ?? 'N/D', 'monto' => $p->monto_pagado, 'fecha' => $p->fecha_pago, 'detalle' => "Recupero Capital"]),
            'intereses' => Pago::where('tipo_pago', 'Interes')->whereBetween('fecha_pago', [$from, $to])->with('prestamo')->get()->map(fn($p) => ['codigo' => $p->prestamo->codigo ?? 'N/D', 'monto' => $p->monto_pagado, 'fecha' => $p->fecha_pago, 'detalle' => "Cobro Interés"]),
            'gastos' => Caja::where('tipo_movimiento', 'Egreso')->where('origen', 'Gasto')->whereBetween('fecha', [$from, $to])->get()->map(fn($g) => ['motivo' => $g->descripcion, 'monto' => $g->monto, 'fecha' => $g->fecha, 'detalle' => "Gasto"]),
            'totales' => [
                'saliente' => Prestamo::whereBetween('fecha_prestamo', [$from, $to])->sum('monto'),
                'entrante' => Pago::where('tipo_pago', 'Capital')->whereBetween('fecha_pago', [$from, $to])->sum('monto_pagado'),
                'intereses' => Pago::where('tipo_pago', 'Interes')->whereBetween('fecha_pago', [$from, $to])->sum('monto_pagado'),
                'gastos' => Caja::where('tipo_movimiento', 'Egreso')->where('origen', 'Gasto')->whereBetween('fecha', [$from, $to])->sum('monto'),
            ]
        ];
    }
}