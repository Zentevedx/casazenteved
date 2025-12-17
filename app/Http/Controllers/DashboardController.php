<?php

namespace App\Http\Controllers;

use App\Models\Prestamo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request) 
    {
        // 1. VERIFICACIÓN AUTOMÁTICA (Reglas de Negocio)
        $prestamosParaVerificar = Prestamo::where('estado', 'Activo')->with('pagos')->get();
        
        foreach ($prestamosParaVerificar as $prestamo) {
            // REGLA 1: El Capital MATA el préstamo (lo paga)
            $totalCapitalPagado = $prestamo->pagos
                ->where('tipo_pago', 'Capital')
                ->sum('monto_pagado'); 

            // Si pagó el capital completo (con margen de error de 0.10 ctvs)
            if ($totalCapitalPagado >= ($prestamo->monto - 0.1)) {
                $prestamo->update(['estado' => 'Pagado']);
                continue; // Se cerró, pasamos al siguiente
            }

            // REGLA 2: Solo el INTERÉS da vida (reinicia el contador)
            $ultimoPagoInteres = $prestamo->pagos
                ->where('tipo_pago', 'Interes')
                ->sortByDesc('fecha_pago')
                ->first();

            // La fecha base es el último interés pagado O la fecha original del préstamo
            $fechaReferencia = $ultimoPagoInteres 
                ? Carbon::parse($ultimoPagoInteres->fecha_pago) 
                : Carbon::parse($prestamo->fecha_prestamo);

            // Si pasaron 3 meses desde esa fecha sin nuevo interés -> VENCIDO
            if ($fechaReferencia->diffInMonths(Carbon::now()) >= 3) {
                $prestamo->update(['estado' => 'Vencido']);
            }
        }

        // 2. PREPARACIÓN DE DATOS PARA LA VISTA
        $estadoFiltro = $request->input('estado', 'Activo'); 
        $query = Prestamo::with(['cliente', 'pagos', 'articulos']);

        if ($estadoFiltro !== 'Todos') {
            $query->where('estado', $estadoFiltro);
        }

        $prestamos = $query->get();
        $prestamosProcesados = [];
        
        // Variables para KPIs globales
        $totalPrestado = 0;
        $totalCapitalRecuperado = 0;
        $totalInteresesGenerados = 0;
        $prestamosEnMora = 0; 

        foreach ($prestamos as $prestamo) {
            // Filtrar pagos
            $pagosInteres = $prestamo->pagos->where('tipo_pago', 'Interes');
            $pagosCapital = $prestamo->pagos->where('tipo_pago', 'Capital');
            
            $capitalRecuperado = $pagosCapital->sum('monto_pagado');
            $interesesGenerados = $pagosInteres->sum('monto_pagado');
            
            // Lógica de fechas
            $numPagosInteres = $pagosInteres->count();
            $fechaBase = Carbon::parse($prestamo->fecha_prestamo);
            
            // Calculamos la próxima fecha de pago basada en cuántos meses de interés ha pagado
            $fechaReferenciaProximo = $fechaBase->copy()->addMonths($numPagosInteres);
            $fechaProximoPago = $fechaReferenciaProximo->copy()->addMonth();

            // Determinamos si está en mora técnica (incluso si está "Activo")
            $estaEnMora = $prestamo->estado === 'Vencido' || 
                          ($prestamo->estado === 'Activo' && $fechaProximoPago->lt(Carbon::now()->startOfDay()));
            
            // Acumuladores globales
            $totalPrestado += $prestamo->monto;
            $totalCapitalRecuperado += $capitalRecuperado;
            $totalInteresesGenerados += $interesesGenerados;
            if ($estaEnMora) $prestamosEnMora++;

            // Formatear Historial de Intereses para el frontend
            $historialIntereses = $pagosInteres->sortByDesc('fecha_pago')->map(function($pago) {
                return [
                    'id' => $pago->id,
                    'fecha' => Carbon::parse($pago->fecha_pago)->format('d/m/Y'),
                    'monto' => $pago->monto_pagado
                ];
            })->values();

            // Formatear Lista de Artículos para el frontend
            $listaArticulos = $prestamo->articulos->map(function($art) {
                return [
                    'nombre' => $art->nombre_articulo,
                    'detalle' => $art->descripcion . ($art->marca ? ' - ' . $art->marca : '') . ($art->modelo ? ' (' . $art->modelo . ')' : ''),
                ];
            });

            // Construcción del objeto final
            $prestamosProcesados[] = [
                'id' => $prestamo->id,
                'codigo' => $prestamo->codigo,
                'monto' => $prestamo->monto,
                'estado' => $prestamo->estado, 
                'cliente_nombre' => $prestamo->cliente->nombre ?? 'Desconocido',
                'cliente_id' => $prestamo->cliente->id ?? null, // <--- CAMBIO CLAVE AQUI
                'fecha_prestamo' => $fechaBase->toDateString(), 
                'fecha_proximo_pago' => $fechaProximoPago->toDateString(),
                'capital_recuperado' => $capitalRecuperado,
                'intereses_generados' => $interesesGenerados,
                'esta_en_mora' => $estaEnMora,
                'historial_intereses' => $historialIntereses,
                'articulos' => $listaArticulos,
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
                    ]
                ];
            })->sortByDesc('mes_anio')->values();

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
        ]);
    }
}