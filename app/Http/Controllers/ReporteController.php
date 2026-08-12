<?php
namespace App\Http\Controllers;

use App\Models\Prestamo;
use App\Services\AgingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ReporteController extends Controller
{
    public function __construct(
        private AgingService $agingService
    ) {}

    public function reporteFinanciero(Request $request)
    {
        $modo = $request->input('modo', 'mensual'); // mensual, semanal, global
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        $week = $request->input('week', 1);

        $queryDate = Carbon::create($year, $month, 1);
        $periodoLabel = '';

        // Determinar rango de fechas según el modo
        if ($modo === 'global') {
            $from = Carbon::create(2000, 1, 1); // Fecha muy antigua
            $to = now()->endOfDay();
            $periodoLabel = 'Histórico Global';
        } elseif ($modo === 'semanal') {
            $from = $queryDate->copy()->startOfMonth()->addWeeks($week - 1)->startOfWeek();
            $to = $from->copy()->endOfWeek();
            if ($from->month != $month) $from = $queryDate->copy()->startOfMonth();
            if ($to->month != $month) $to = $queryDate->copy()->endOfMonth();
            $periodoLabel = "Semana $week de " . $queryDate->translatedFormat('F Y');
        } else { // mensual
            $from = $queryDate->copy()->startOfMonth();
            $to = $queryDate->copy()->endOfMonth();
            $periodoLabel = $queryDate->translatedFormat('F Y');
        }

        // 1. Préstamos del periodo
        $prestamosQuery = Prestamo::with('cliente')->whereBetween('fecha_prestamo', [$from, $to])->latest('fecha_prestamo');
        $listaPrestamos = $prestamosQuery->get();
        $prestamosDelMes = $listaPrestamos->sum('monto');
        $cantidadPrestamos = $listaPrestamos->count();

        // 2. Intereses del periodo
        $interesesQuery = \App\Models\Pago::with(['prestamo.cliente', 'prestamo'])->whereBetween('fecha_pago', [$from, $to])->where('tipo_pago', 'Interes')->latest('fecha_pago');
        $listaIntereses = $interesesQuery->get();
        $interesesDelMes = $listaIntereses->sum('monto_pagado');

        // 3. Gastos del periodo
        $gastosQuery = \App\Models\Caja::whereBetween('fecha', [$from, $to])->where('origen', 'Gasto')->latest('fecha');
        $listaGastos = $gastosQuery->get();
        $gastosDelMes = $listaGastos->sum('monto');

        // 4. Capital recuperado del periodo
        $capitalQuery = \App\Models\Pago::with(['prestamo.cliente', 'prestamo'])->whereBetween('fecha_pago', [$from, $to])->where('tipo_pago', 'Capital')->latest('fecha_pago');
        $listaCapital = $capitalQuery->get();
        $capitalRecuperado = $listaCapital->sum('monto_pagado');

                // 5. CÁLCULO DE CARTERA (HISTÓRICO / "TIME TRAVEL") usando AgingService
        $fechaCorte = $to->isFuture() ? now() : $to->copy();
        
        $carteraTotalQuery = Prestamo::with(['cliente', 'pagos', 'articulos'])
            ->where('fecha_prestamo', '<=', $fechaCorte)
            ->get();
            
        $loansActiveAtDate = $carteraTotalQuery->filter(function ($p) use ($fechaCorte) {
            $aging = $this->agingService->getAgingData($p, $fechaCorte);
            $p->saldo_a_fecha = $aging['saldo_pendiente'];
            $p->aging_data = $aging;
            return $p->saldo_a_fecha > 1;
        });

        $carteraTotal = $loansActiveAtDate->sum('saldo_a_fecha');
        
        // Filtrar Remate usando AgingService (>= 3 meses de retraso = remate)
        $prestamosRemate = $loansActiveAtDate->filter(function ($p) use ($fechaCorte) {
            $mesesRetraso = $this->agingService->getMonthsOverdue($p, $fechaCorte);
            if ($mesesRetraso < 3) return false;

            $p->fecha_ultimo_pago   = $p->aging_data['ultimo_pago_fecha'];
            $p->dias_sin_pago       = $p->aging_data['dias_retraso'];
            $p->meses_sin_pago      = $mesesRetraso;
            $p->cliente_id_ref      = $p->cliente->id ?? null;

            return true;
        })->values();

        $carteraRemate = $prestamosRemate->sum('saldo_a_fecha');
        $carteraVigente = $carteraTotal - $carteraRemate;

        // 6. SALUD DE CARTERA — Aging + Eficiencia de Cobranza + Alertas Tempranas
        $aging = [
            'al_dia'      => ['count' => 0, 'monto' => 0],   // 0-30 días retraso
            'riesgo_leve' => ['count' => 0, 'monto' => 0],   // 31-60 días
            'riesgo_alto' => ['count' => 0, 'monto' => 0],   // 61-89 días
            'remate'      => ['count' => 0, 'monto' => 0],   // >= 90 días
        ];
        $alertas = [];

        foreach ($loansActiveAtDate as $p) {
            $diasRetraso = $p->aging_data['dias_retraso'];
            $categoria = $p->aging_data['categoria_aging'];

            if ($categoria === 'verde') {
                $aging['al_dia']['count']++;
                $aging['al_dia']['monto'] += $p->saldo_a_fecha;
            } elseif ($categoria === 'amarillo') {
                $aging['riesgo_leve']['count']++;
                $aging['riesgo_leve']['monto'] += $p->saldo_a_fecha;
                $alertas[] = $this->crearAlerta($p, 'leve');
            } elseif ($categoria === 'rojo') {
                $aging['riesgo_alto']['count']++;
                $aging['riesgo_alto']['monto'] += $p->saldo_a_fecha;
                $alertas[] = $this->crearAlerta($p, 'alto');
            } else {
                $aging['remate']['count']++;
                $aging['remate']['monto'] += $p->saldo_a_fecha;
            }
        }

        // Interés proyectado = 10% de la cartera vigente (sin los de remate)
        $interesProyectado     = round($carteraVigente * 0.10, 2);
        $eficienciaCobranza    = $interesProyectado > 0
            ? round(($interesesDelMes / $interesProyectado) * 100, 1)
            : 0;

        usort($alertas, fn($a, $b) => $b['dias_sin_pago'] - $a['dias_sin_pago']);

        return Inertia::render('Reportes/Financiero', [
            'stats' => [
                'prestamos'          => $prestamosDelMes,
                'cantidad_prestamos' => $cantidadPrestamos,
                'intereses'          => $interesesDelMes,
                'gastos'             => $gastosDelMes,
                'capital_recuperado' => $capitalRecuperado,
                'cartera_total'      => $carteraTotal,
                'cartera_remate'     => $carteraRemate,
                'cartera_vigente'    => $carteraVigente,
            ],
            'listas' => [
                'prestamos' => $listaPrestamos,
                'intereses' => $listaIntereses,
                'gastos'    => $listaGastos,
                'capital'   => $listaCapital,
            ],
            'salud' => [
                'aging'               => $aging,
                'interes_proyectado'  => $interesProyectado,
                'eficiencia_cobranza' => $eficienciaCobranza,
                'alertas'             => $alertas,
            ],
            'prestamosRemate' => $prestamosRemate,
            'filters' => [
                'modo'        => $modo,
                'year'        => (int)$year,
                'month'       => (int)$month,
                'week'        => (int)$week,
                'periodoLabel' => $periodoLabel,
            ]
        ]);
    }

    /**
     * Helper para crear alerta temprana
     */
    private function crearAlerta($p, string $nivel): array
    {
        return [
            'id'            => $p->id,
            'codigo'        => $p->codigo,
            'cliente_id'    => $p->cliente->id ?? null,
            'cliente'       => $p->cliente->nombre ?? 'N/A',
            'telefono'      => $p->cliente->telefono ?? null,
            'monto'         => $p->saldo_a_fecha,
            'dias_sin_pago' => $p->aging_data['dias_retraso'] ?? 0,
            'nivel'         => $nivel,
            'meses_retraso' => $p->aging_data['meses_retraso'] ?? 0,
        ];
    }

    public function generarPdfFinanciero(Request $request)
    {
        $modo = $request->input('modo', 'mensual');
        $tipo = $request->input('tipo', 'resumen'); // resumen, prestamos, intereses, gastos, capital, remate
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        $week = $request->input('week', 1);

        $queryDate = Carbon::create($year, $month, 1);
        $periodoLabel = '';

        if ($modo === 'global') {
            $from = Carbon::create(2000, 1, 1);
            $to = now()->endOfDay();
            $periodoLabel = 'Histórico Global';
        } elseif ($modo === 'semanal') {
            $from = $queryDate->copy()->startOfMonth()->addWeeks($week - 1)->startOfWeek();
            $to = $from->copy()->endOfWeek();
            if ($from->month != $month) $from = $queryDate->copy()->startOfMonth();
            if ($to->month != $month) $to = $queryDate->copy()->endOfMonth();
            $periodoLabel = "Semana $week de " . $queryDate->translatedFormat('F Y');
        } else {
            $from = $queryDate->copy()->startOfMonth();
            $to = $queryDate->copy()->endOfMonth();
            $periodoLabel = $queryDate->translatedFormat('F Y');
        }

        // Datos base (Totales)
        $data = [
            'tipo' => $tipo,
            'titulo' => 'Reporte Financiero',
            'periodo' => $periodoLabel,
            'fecha_generacion' => now()->format('d/m/Y H:i'),
            'listas' => []
        ];

        // Calcular Totales siempre para el encabezado
        $prestamosQuery = Prestamo::with('cliente', 'articulos')->whereBetween('fecha_prestamo', [$from, $to])->latest('fecha_prestamo');
        $listaPrestamos = $prestamosQuery->get();
        $data['prestamos'] = $listaPrestamos->sum('monto');
        $data['cantidad_prestamos'] = $listaPrestamos->count();

        $interesesQuery = \App\Models\Pago::with(['prestamo.cliente', 'prestamo'])->whereBetween('fecha_pago', [$from, $to])->where('tipo_pago', 'Interes')->latest('fecha_pago');
        $listaIntereses = $interesesQuery->get();
        $data['intereses'] = $listaIntereses->sum('monto_pagado');

        $gastosQuery = \App\Models\Caja::whereBetween('fecha', [$from, $to])->where('origen', 'Gasto')->latest('fecha');
        $listaGastos = $gastosQuery->get();
        $data['gastos'] = $listaGastos->sum('monto');

        $capitalQuery = \App\Models\Pago::with(['prestamo.cliente', 'prestamo'])->whereBetween('fecha_pago', [$from, $to])->where('tipo_pago', 'Capital')->latest('fecha_pago');
        $listaCapital = $capitalQuery->get();
        $data['capital_recuperado'] = $listaCapital->sum('monto_pagado');

        // 5. CÁLCULO DE CARTERA (HISTÓRICO / "TIME TRAVEL")
        // Definir la fecha de corte exacta: si el periodo aún no termina (ej. fin de mes actual), cortar hoy.
        $fechaCorte = $to->isFuture() ? now() : $to->copy();
        
        // Obtener todos los préstamos creados hasta el final del periodo seleccionado
        $carteraTotalQuery = Prestamo::with(['cliente', 'pagos', 'articulos'])
            ->where('fecha_prestamo', '<=', $fechaCorte)
            ->get();
            
        // Filtrar aquellos que tenían saldo pendiente a la fecha de corte
        $loansActiveAtDate = $carteraTotalQuery->filter(function ($p) use ($fechaCorte) {
            $capitalPagadoHastaFecha = $p->pagos
                ->where('tipo_pago', 'Capital')
                ->where('fecha_pago', '<=', $fechaCorte)
                ->sum('monto_pagado');
            
            // Calculamos el saldo a esa fecha
            $p->saldo_a_fecha = $p->monto - $capitalPagadoHastaFecha;
            
            // Si debía algo (más de 1 Bs para evitar decimales residuales), estaba activo
            return $p->saldo_a_fecha > 1;
        });

        // La Cartera Total es la suma del Saldo Pendiente real en ese momento
        $carteraTotal = $loansActiveAtDate->sum('saldo_a_fecha');
        
        // Filtrar Remate (Riesgo) usando la fecha de corte como referencia
        $prestamosRemate = $loansActiveAtDate->filter(function ($p) use ($fechaCorte) {
            $fInicio = Carbon::parse($p->fecha_prestamo);
            
            // Buscar último movimiento hasta la fecha de corte
            $ultimoPago = $p->pagos
                ->where('fecha_pago', '<=', $fechaCorte)
                ->sortByDesc('fecha_pago')
                ->first();
            
            $fechaReferencia = $ultimoPago ? Carbon::parse($ultimoPago->fecha_pago) : $fInicio;
            
            $mesesSinPago = (int) $fechaReferencia->startOfDay()->diffInMonths($fechaCorte->copy()->startOfDay());

            if ($mesesSinPago < 3) return false;

            // Enriquecer con campos calculados para el frontend
            $p->fecha_ultimo_pago   = $ultimoPago ? $ultimoPago->fecha_pago : null;
            $p->dias_sin_pago       = (int) $fechaReferencia->startOfDay()->diffInDays($fechaCorte->copy()->startOfDay());
            $p->meses_sin_pago      = $mesesSinPago;

            return true;
        })->values();

        $carteraRemate = $prestamosRemate->sum('saldo_a_fecha');
        $carteraVigente = $carteraTotal - $carteraRemate;

        $data['prestamosRemate'] = $prestamosRemate;
        $data['cartera_total'] = $carteraTotal;
        $data['cartera_remate'] = $carteraRemate;
        $data['cartera_vigente'] = $carteraVigente;

        // Configurar Título y Datos específicos según el tipo
        if ($tipo === 'remate' && isset($data['prestamosRemate'])) {
            $data['prestamosRemate'] = collect($data['prestamosRemate'])->map(function ($p) {
                return (object) [
                    'codigo'            => $p['codigo'] ?? $p->codigo,
                    'cliente'           => (object) [
                        'nombre'   => $p['cliente_nombre'] ?? $p->cliente->nombre ?? 'N/A',
                        'telefono' => $p['telefono'] ?? $p->cliente->telefono ?? 'N/A',
                        'ci'       => $p['ci'] ?? $p->cliente->ci ?? 'N/A',
                    ],
                    'articulos'         => $p['articulos'] ?? $p->articulos ?? [],
                    'fecha_prestamo'    => $p['fecha_prestamo'] ?? $p->fecha_prestamo,
                    'fecha_ultimo_pago' => $p['fecha_ultimo_pago'] ?? $p->aging_data['ultimo_pago_fecha'] ?? null,
                    'dias_sin_pago'     => $p['dias_sin_pago'] ?? $p->aging_data['dias_retraso'] ?? 0,
                    'meses_sin_pago'    => $p['meses_sin_pago'] ?? $p->aging_data['meses_retraso'] ?? 0,
                    'saldo_a_fecha'     => $p['saldo_a_fecha'] ?? $p->saldo_a_fecha ?? 0,
                    'monto'             => $p['monto'] ?? $p->monto,
                ];
            });
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.reporte-financiero', $data);

        // Orientación horizontal para el remate (9 columnas)
        if ($tipo === 'remate') {
            $pdf->setPaper('letter', 'landscape');
        }

        $filename = 'reporte-' . $tipo . '-' . str_replace(' ', '-', strtolower($periodoLabel)) . '.pdf';
        return $pdf->download($filename);
    }

    public function exportarExcelFinanciero(Request $request)
    {
        $modo = $request->input('modo', 'mensual');
        $tipo = $request->input('tipo', 'resumen'); 
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        $week = $request->input('week', 1);

        $queryDate = Carbon::create($year, $month, 1);
        $periodoLabel = '';

        if ($modo === 'global') {
            $from = Carbon::create(2000, 1, 1);
            $to = now()->endOfDay();
            $periodoLabel = 'Histórico Global';
        } elseif ($modo === 'semanal') {
            $from = $queryDate->copy()->startOfMonth()->addWeeks($week - 1)->startOfWeek();
            $to = $from->copy()->endOfWeek();
            if ($from->month != $month) $from = $queryDate->copy()->startOfMonth();
            if ($to->month != $month) $to = $queryDate->copy()->endOfMonth();
            $periodoLabel = "Semana $week de " . $queryDate->translatedFormat('F Y');
        } else {
            $from = $queryDate->copy()->startOfMonth();
            $to = $queryDate->copy()->endOfMonth();
            $periodoLabel = $queryDate->translatedFormat('F Y');
        }

        $data = [
            'tipo' => $tipo,
            'titulo' => 'Reporte Financiero',
            'periodo' => $periodoLabel,
            'fecha_generacion' => now()->format('d/m/Y H:i'),
            'listas' => []
        ];

        $prestamosQuery = Prestamo::with('cliente', 'articulos')->whereBetween('fecha_prestamo', [$from, $to])->latest('fecha_prestamo');
        $listaPrestamos = $prestamosQuery->get();
        $data['prestamos'] = $listaPrestamos->sum('monto');
        $data['cantidad_prestamos'] = $listaPrestamos->count();

        $interesesQuery = \App\Models\Pago::with(['prestamo.cliente', 'prestamo'])->whereBetween('fecha_pago', [$from, $to])->where('tipo_pago', 'Interes')->latest('fecha_pago');
        $listaIntereses = $interesesQuery->get();
        $data['intereses'] = $listaIntereses->sum('monto_pagado');

        $gastosQuery = \App\Models\Caja::whereBetween('fecha', [$from, $to])->where('origen', 'Gasto')->latest('fecha');
        $listaGastos = $gastosQuery->get();
        $data['gastos'] = $listaGastos->sum('monto');

        $capitalQuery = \App\Models\Pago::with(['prestamo.cliente', 'prestamo'])->whereBetween('fecha_pago', [$from, $to])->where('tipo_pago', 'Capital')->latest('fecha_pago');
        $listaCapital = $capitalQuery->get();
        $data['capital_recuperado'] = $listaCapital->sum('monto_pagado');

        $fechaCorte = $to->isFuture() ? now() : $to->copy();

        $carteraTotalQuery = Prestamo::with(['cliente', 'pagos', 'articulos'])
            ->where('fecha_prestamo', '<=', $fechaCorte)
            ->get();
            
        $loansActiveAtDate = $carteraTotalQuery->filter(function ($p) use ($fechaCorte) {
            $capitalPagadoHastaFecha = $p->pagos
                ->where('tipo_pago', 'Capital')
                ->where('fecha_pago', '<=', $fechaCorte)
                ->sum('monto_pagado');
            $p->saldo_a_fecha = $p->monto - $capitalPagadoHastaFecha;
            return $p->saldo_a_fecha > 1;
        });

        $carteraTotal = $loansActiveAtDate->sum('saldo_a_fecha');
        
        $prestamosRemate = $loansActiveAtDate->filter(function ($p) use ($fechaCorte) {
            $ultimoPago = $p->pagos
                ->where('fecha_pago', '<=', $fechaCorte)
                ->sortByDesc('fecha_pago')
                ->first();

            $fechaReferencia = $ultimoPago
                ? Carbon::parse($ultimoPago->fecha_pago)
                : Carbon::parse($p->fecha_prestamo);

            $mesesSinPago = (int) $fechaReferencia->startOfDay()->diffInMonths($fechaCorte->copy()->startOfDay());

            if ($mesesSinPago < 3) return false;

            // Enriquecer con campos para el blade (PDF y Excel)
            $p->fecha_ultimo_pago = $ultimoPago ? $ultimoPago->fecha_pago : null;
            $p->dias_sin_pago     = (int) $fechaReferencia->startOfDay()->diffInDays($fechaCorte->copy()->startOfDay());
            $p->meses_sin_pago    = $mesesSinPago;

            return true;
        })->values();

        $carteraRemate  = $prestamosRemate->sum('saldo_a_fecha');
        $carteraVigente = $carteraTotal - $carteraRemate;

        $data['prestamosRemate'] = $prestamosRemate;
        $data['cartera_total'] = $carteraTotal;
        $data['cartera_remate'] = $carteraRemate;
        $data['cartera_vigente'] = $carteraVigente;

        switch ($tipo) {
            case 'prestamos':
                $data['titulo'] = 'Detalle de Préstamos Otorgados';
                $data['listas']['prestamos'] = $listaPrestamos;
                break;
            case 'intereses':
                $data['titulo'] = 'Detalle de Intereses Cobrados';
                $data['listas']['intereses'] = $listaIntereses;
                break;
            case 'gastos':
                $data['titulo'] = 'Detalle de Gastos Operativos';
                $data['listas']['gastos'] = $listaGastos;
                break;
            case 'capital':
                $data['titulo'] = 'Detalle de Capital Recuperado';
                $data['listas']['capital'] = $listaCapital;
                break;
            case 'remate':
                $data['titulo'] = 'Reporte de Préstamos en Remate';
                // Mapear a array plano: los atributos dinámicos del closure no siempre
                // se serializan bien hacia la vista Blade de Excel
                $data['prestamosRemate'] = $prestamosRemate->map(function ($p) {
                    return (object) [
                        'codigo'           => $p->codigo,
                        'cliente'          => (object) [
                            'nombre'   => $p->cliente->nombre ?? 'N/A',
                            'telefono' => $p->cliente->telefono ?? 'N/A',
                            'ci'       => $p->cliente->ci ?? 'N/A',
                        ],
                        'articulos'        => $p->articulos,
                        'fecha_prestamo'   => $p->fecha_prestamo,
                        'fecha_ultimo_pago'=> $p->fecha_ultimo_pago,
                        'dias_sin_pago'    => $p->dias_sin_pago,
                        'meses_sin_pago'   => $p->meses_sin_pago,
                        'saldo_a_fecha'    => $p->saldo_a_fecha,
                        'monto'            => $p->monto,
                    ];
                });
                break;
            default:
                $data['titulo'] = 'Resumen Financiero - Gestión de Ganancias';
                break;
        }

        $filename = 'reporte-' . $tipo . '-' . str_replace(' ', '-', strtolower($periodoLabel)) . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ReporteFinancieroExport($data), $filename);
    }
}