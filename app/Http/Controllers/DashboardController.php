<?php

namespace App\Http\Controllers;

use App\Models\Prestamo;
use App\Services\AgingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct(
        private AgingService $agingService
    ) {}

    public function index(Request $request) 
    {
        // 1. VERIFICACIÓN AUTOMÁTICA usando AgingService
        $prestamosParaVerificar = Prestamo::whereIn('estado', ['Activo', 'Vencido'])->with(['pagos', 'cliente'])->get();
        
        foreach ($prestamosParaVerificar as $prestamo) {
            // REGLA 1: Capital completo pagado = cerrar préstamo
            if ($this->agingService->isCapitalFullyPaid($prestamo)) {
                $prestamo->update(['estado' => 'Pagado']);
                $prestamo->articulos()->update(['estado' => 'Retirado']);
                continue;
            }

            // REGLA 2: Más de 90 días de retraso = Vencido
            $diasRetraso = $this->agingService->getDaysOverdue($prestamo);
            if ($diasRetraso >= 90 && $prestamo->estado !== 'Vencido') {
                $prestamo->update(['estado' => 'Vencido']);
            }
        }

        // 2. PREPARACIÓN DE DATOS PARA LA VISTA
        $estadoFiltro = $request->input('estado', 'todos_pendientes'); 
        
        // Cargamos TODOS los préstamos y los filtramos en PHP usando AgingService
        // para tener datos consistentes (no filtrar por estado DB para categorías custom)
        $allPrestamos = Prestamo::with(['cliente', 'pagos', 'articulos'])->get();
        
        // Procesar todos y aplicar aging para filtrado
        $prestamosProcesados = [];
        $totalPrestado = 0;
        $totalCapitalRecuperado = 0;
        $totalInteresesGenerados = 0;
        $prestamosEnMora = 0;

        foreach ($allPrestamos as $prestamo) {
            $aging = $this->agingService->getAgingData($prestamo);

            // Aplicar filtros basados en categoría de aging
            $incluir = false;
            switch ($estadoFiltro) {
                case 'todos_pendientes':
                    $incluir = $prestamo->estado !== 'Pagado';
                    break;
                case 'al_dia':
                    $incluir = $aging['categoria_aging'] === 'verde' && $prestamo->estado !== 'Pagado';
                    break;
                case 'riesgo_leve':
                    $incluir = $aging['categoria_aging'] === 'amarillo';
                    break;
                case 'riesgo_alto':
                    $incluir = $aging['categoria_aging'] === 'rojo';
                    break;
                case 'remate':
                    $incluir = $aging['categoria_aging'] === 'remate';
                    break;
                case 'Activo':
                    $incluir = $prestamo->estado === 'Activo';
                    break;
                case 'Vencido':
                    $incluir = $prestamo->estado === 'Vencido';
                    break;
                case 'Pagado':
                    $incluir = $prestamo->estado === 'Pagado';
                    break;
                default:
                    $incluir = true;
            }

            if (!$incluir) continue;

            // Usar AgingService para cálculos unificados (ya calculado arriba)
            
            $capitalRecuperado = $prestamo->pagos->where('tipo_pago', 'Capital')->sum('monto_pagado');
            $interesesGenerados = $prestamo->pagos->where('tipo_pago', 'Interes')->sum('monto_pagado');
            
            $estaEnMora = $aging['dias_retraso'] > 0;
            
            $totalPrestado += $prestamo->monto;
            $totalCapitalRecuperado += $capitalRecuperado;
            $totalInteresesGenerados += $interesesGenerados;
            if ($estaEnMora) $prestamosEnMora++;

            $historialIntereses = $prestamo->pagos->where('tipo_pago', 'Interes')->sortByDesc('fecha_pago')->map(function($pago) {
                return [
                    'id' => $pago->id,
                    'fecha' => Carbon::parse($pago->fecha_pago)->format('d/m/Y'),
                    'monto' => $pago->monto_pagado
                ];
            })->values();

            $listaArticulos = $prestamo->articulos->map(function($art) {
                return [
                    'id'     => $art->id,
                    'nombre' => $art->nombre_articulo,
                    'detalle' => $art->descripcion ?? '',
                    'foto'   => $art->foto_url,
                    'estado' => $art->estado,
                ];
            });

            $prestamosProcesados[] = [
                'id'                   => $prestamo->id,
                'codigo'               => $prestamo->codigo,
                'monto'                => $prestamo->monto,
                'estado'               => $prestamo->estado, 
                'cliente_nombre'       => $prestamo->cliente->nombre ?? 'Desconocido',
                'cliente_id'           => $prestamo->cliente->id ?? null,
                'fecha_prestamo'       => $prestamo->fecha_prestamo,
                'fecha_vencimiento'    => $aging['fecha_vencimiento'],
                'fecha_al_dia'         => $aging['fecha_al_dia'],
                'capital_recuperado'   => $capitalRecuperado,
                'intereses_generados'  => $interesesGenerados,
                'esta_en_mora'         => $estaEnMora,
                'dias_retraso'         => $aging['dias_retraso'],
                'meses_retraso'        => $aging['meses_retraso'],
                'categoria_aging'      => $aging['categoria_aging'],
                'saldo_pendiente'      => $aging['saldo_pendiente'],
                'historial_intereses'  => $historialIntereses,
                'articulos'            => $listaArticulos,
            ];
        }
        
        // 3. AGRUPACIÓN (Mes -> Semana)
        $reporteAgrupado = collect($prestamosProcesados)
            ->groupBy(fn($item) => Carbon::parse($item['fecha_prestamo'])->format('Y-m'))
            ->map(function ($prestamosDelMes, $mesAnio) {
                $prestamosPorSemana = $prestamosDelMes
                    ->groupBy(fn($item) => Carbon::parse($item['fecha_prestamo'])->weekOfYear)
                    ->map(function ($prestamosDeLaSemana, $numeroSemana) use ($mesAnio) {
                        $anio = substr($mesAnio, 0, 4);
                        // Calculamos inicio y fin de la semana para mostrar rango
                        $fechaInicioSemana = Carbon::now()->setISODate($anio, $numeroSemana, 1)->startOfDay(); 
                        $fechaFinSemana = Carbon::now()->setISODate($anio, $numeroSemana, 7)->endOfDay(); 

                        return [
                            'semana' => $numeroSemana,
                            'rango_fechas' => $fechaInicioSemana->translatedFormat('d M') . ' - ' . $fechaFinSemana->translatedFormat('d M'),
                            'prestamos' => $prestamosDeLaSemana->values(),
                            'resumen' => [
                                'monto_total' => $prestamosDeLaSemana->sum('monto'),
                                'capital_recuperado' => $prestamosDeLaSemana->sum('capital_recuperado'),
                                'intereses_generados' => $prestamosDeLaSemana->sum('intereses_generados'),
                            ]
                        ];
                    })->sortBy('semana')->values();

                return [
                    'mes_anio' => $mesAnio,
                    'nombre_mes' => Carbon::createFromFormat('Y-m', $mesAnio)->translatedFormat('F Y'),
                    'semanas' => $prestamosPorSemana,
                    'resumen' => [
                        'monto_total' => $prestamosDelMes->sum('monto'),
                        'capital_recuperado' => $prestamosDelMes->sum('capital_recuperado'),
                        'intereses_generados' => $prestamosDelMes->sum('intereses_generados'),
                    ],
                    'contadores' => [
                        'total'    => $prestamosDelMes->count(),
                        'verde'    => $prestamosDelMes->filter(fn($p) => $p['categoria_aging'] === 'verde' && $p['estado'] !== 'Pagado')->count(),
                        'amarillo' => $prestamosDelMes->filter(fn($p) => $p['categoria_aging'] === 'amarillo')->count(),
                        'rojo'     => $prestamosDelMes->filter(fn($p) => $p['categoria_aging'] === 'rojo')->count(),
                        'remate'   => $prestamosDelMes->filter(fn($p) => $p['categoria_aging'] === 'remate')->count(),
                        'pagado'   => $prestamosDelMes->filter(fn($p) => $p['estado'] === 'Pagado')->count(),
                    ]
                ];
            })->sortByDesc('mes_anio')->values();

        // 5. ALERTA DE NEGOCIO (NUEVO)
        $alertas = [
            'criticos' => Prestamo::where('estado', '!=', 'Pagado')
                ->where(function($q) {
                    $q->where('estado', 'Vencido')
                      ->orWhereDate('fecha_prestamo', '<', Carbon::now()->subDays(90));
                })->count(),
            
            'por_vencer' => Prestamo::where('estado', 'Activo')
                ->whereHas('pagos', function($q) {
                     // Lógica simplificada: si no pagó en el último mes
                     $q->select('prestamo_id')->groupBy('prestamo_id')
                       ->havingRaw('MAX(fecha_pago) < ?', [Carbon::now()->subDays(25)]);
                })->orWhereDoesntHave('pagos', function($q) {
                     // O si es nuevo y ya casi cumple el mes
                     $q->whereDate('fecha_prestamo', '<', Carbon::now()->subDays(25));
                })->count()
        ];

        // 6. TOP DEUDORES (Real Deuda Pendiente)
        // Obtenemos todos los préstamos NO pagados
        $prestamosActivos = Prestamo::where('estado', '!=', 'Pagado')
            ->with(['pagos', 'cliente'])
            ->get();

        $deudores = [];

        foreach ($prestamosActivos as $p) {
            $capitalPagado = $p->pagos->where('tipo_pago', 'Capital')->sum('monto_pagado');
            $saldoPendiente = $p->monto - $capitalPagado;

            if ($saldoPendiente <= 0) continue; // Si ya no debe capital, no cuenta

            $clienteId = $p->cliente_id;
            
            if (!isset($deudores[$clienteId])) {
                $deudores[$clienteId] = [
                    'id' => $clienteId,
                    'nombre' => $p->cliente->nombre . ' ' . $p->cliente->apellido,
                    'foto_url' => $p->cliente->foto_url ?? null,
                    'total_deuda' => 0,
                    'cantidad' => 0
                ];
            }

            $deudores[$clienteId]['total_deuda'] += $saldoPendiente;
            $deudores[$clienteId]['cantidad']++;
        }

        // Ordenar por deuda descendente y tomar top 5
        $topDeudores = collect($deudores)
            ->sortByDesc('total_deuda')
            ->take(5)
            ->values()
            ->all();

        // 4. RESPUESTA A INERTIA
        return Inertia::render('Dashboard', [
            'reporteAgrupado' => $reporteAgrupado,
            'estadoFiltro' => $estadoFiltro,
            'indicadores' => [
                'total_prestado' => $totalPrestado,
                'total_capital_recuperado' => $totalCapitalRecuperado,
                'total_intereses_generados' => $totalInteresesGenerados,
                'total_prestamos_en_mora' => $prestamosEnMora,
            ],
            'alertas' => $alertas,
            'topDeudores' => $topDeudores
        ]);
    }
}