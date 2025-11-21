<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Pago;
use App\Models\Prestamo;
use App\Models\Caja;
use Carbon\Carbon;

class EstadisticasController extends Controller
{
    public function index(Request $request)
    {
        $modo = $request->input('modo', 'semana');
        $fecha = $request->input('fecha', now()->toDateString());

        switch ($modo) {
            case 'dia':
                $from = $to = Carbon::parse($fecha);
                break;
            case 'semana':
                $from = Carbon::parse($fecha)->startOfWeek();
                $to = Carbon::parse($fecha)->endOfWeek();
                break;
            case 'mes':
                $from = Carbon::parse($fecha)->startOfMonth();
                $to = Carbon::parse($fecha)->endOfMonth();
                break;
            default:
                $from = Carbon::now()->startOfWeek();
                $to = Carbon::now()->endOfWeek();
        }

        $fechas = collect();
        $interes = collect();
        $gastos = collect();
        $prestamos = collect();
        $rentabilidad = collect();

        $period = new \DatePeriod(
            $from,
            \DateInterval::createFromDateString('1 day'),
            $to->copy()->addDay()
        );

        foreach ($period as $date) {
            $label = $modo === 'dia' ? $date->format('d/m') : $date->format('d M');
            $fechas->push($label);

            $interesDia = Pago::whereDate('fecha_pago', $date)
                ->where('tipo_pago', 'Interes')
                ->sum('monto_pagado');

            $gastoDia = Caja::where('tipo_movimiento', 'Egreso')
                ->where('origen', 'Gasto')
                ->whereDate('fecha', $date)
                ->sum('monto');

            $prestamosDia = Prestamo::whereDate('fecha_prestamo', $date)->count();

            $interes->push($interesDia);
            $gastos->push($gastoDia);
            $prestamos->push($prestamosDia);
            $rentabilidad->push($interesDia - $gastoDia);
        }

        $reporteCaja = [
            'capital_saliente' => Prestamo::whereBetween('fecha_prestamo', [$from, $to])->get()->map(fn($p) => [
                'codigo' => $p->codigo,
                'monto' => $p->monto,
                'fecha' => $p->fecha_prestamo,
                'detalle' => "Cliente ID: {$p->cliente_id}"
            ]),
            'capital_entrante' => Pago::where('tipo_pago', 'Capital')
                ->whereBetween('fecha_pago', [$from, $to])
                ->with('prestamo')->get()->map(fn($p) => [
                    'codigo' => optional($p->prestamo)->codigo ?? 'N/D',
                    'monto' => $p->monto_pagado,
                    'fecha' => $p->fecha_pago,
                    'detalle' => "Pago de capital"
                ]),
            'intereses' => Pago::where('tipo_pago', 'Interes')
                ->whereBetween('fecha_pago', [$from, $to])
                ->with('prestamo')->get()->map(fn($p) => [
                    'codigo' => optional($p->prestamo)->codigo ?? 'N/D',
                    'monto' => $p->monto_pagado,
                    'fecha' => $p->fecha_pago,
                    'detalle' => "Pago de interés"
                ]),
            'gastos' => Caja::where('tipo_movimiento', 'Egreso')
                ->where('origen', 'Gasto')
                ->whereBetween('fecha', [$from, $to])
                ->get()->map(fn($g) => [
                    'motivo' => $g->descripcion,
                    'monto' => $g->monto,
                    'fecha' => $g->fecha,
                    'detalle' => "Gasto operativo"
                ]),
        ];

        $reporteCaja['totales'] = [
            'saliente' => $reporteCaja['capital_saliente']->sum('monto'),
            'entrante' => $reporteCaja['capital_entrante']->sum('monto'),
            'intereses' => $reporteCaja['intereses']->sum('monto'),
            'gastos' => $reporteCaja['gastos']->sum('monto'),
        ];

        return Inertia::render('Estadisticas', [
            'reporteCaja' => $reporteCaja,
            'fechas' => $fechas,
            'interes' => $interes,
            'gastos' => $gastos,
            'prestamos' => $prestamos,
            'rentabilidad' => $rentabilidad,
            'modo' => $modo,
            'fecha' => $fecha,
            'from' => $from->toDateString(),
            'to' => $to->toDateString()
        ]);
    }
}
