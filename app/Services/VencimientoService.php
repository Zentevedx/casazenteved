<?php

namespace App\Services;

use App\Models\Prestamo;
use Carbon\Carbon;

class VencimientoService
{
    /**
     * Niveles de envejecimiento (Traffic Light Protocol + Azul).
     */
    const AGING_LEVELS = [
        'verde'    => ['min' => 0,   'max' => 30,  'label' => 'Estable',              'emoji' => '🟢'],
        'amarillo' => ['min' => 31,  'max' => 60,  'label' => 'Advertencia',           'emoji' => '🟡'],
        'rojo'     => ['min' => 61,  'max' => 90,  'label' => 'Crítico',               'emoji' => '🔴'],
        'azul'     => ['min' => 91,  'max' => 120, 'label' => 'Debería estar vendido', 'emoji' => '🔵'],
        'remate'   => ['min' => 121, 'max' => 9999,'label' => 'En Remate / Subasta',   'emoji' => '⚫'],
    ];

    /**
     * Calcular la fecha de vencimiento para un préstamo.
     * Si tipo_pago === 'Interes', cada pago extiende 1 mes desde la fecha base.
     * Carbon maneja overflow de meses (31 Ene → 28 Feb) automáticamente — ISO-8601.
     */
    public function calcularFechaVencimiento(Prestamo $prestamo): Carbon
    {
        $fechaBase = Carbon::parse($prestamo->fecha_prestamo);
        $mesesPagados = $prestamo->pagos
            ->where('tipo_pago', 'Interes')
            ->count();

        // La fecha de vencimiento es el inicio + meses pagados + 1 mes de gracia
        return $fechaBase->copy()->addMonths($mesesPagados + 1);
    }

    /**
     * Calcular el aging (días de envejecimiento) de un préstamo.
     * Se mide desde la fecha de vencimiento hasta hoy.
     * Si aún no venció, retorna 0 (está al día).
     */
    public function calcularDiasEnvejecimiento(Prestamo $prestamo, Carbon $hoy): int
    {
        $fechaVencimiento = $this->calcularFechaVencimiento($prestamo);

        if ($hoy->lt($fechaVencimiento)) {
            return 0; // Aún no venció
        }

        return (int) $fechaVencimiento->diffInDays($hoy);
    }

    /**
     * Determinar el estado de envejecimiento según los días.
     */
    public function determinarEstadoEnvejecimiento(int $dias): string
    {
        foreach (self::AGING_LEVELS as $key => $level) {
            if ($dias >= $level['min'] && $dias <= $level['max']) {
                return $key;
            }
        }
        return 'remate';
    }

    /**
     * Construir los 4 bloques semanales + lista de recuperación.
     * Retorna la estructura completa del dashboard de cobranza.
     */
    public function construirTablero(): array
    {
        $hoy = Carbon::today('America/La_Paz');

        // Traer todos los préstamos activos/vencidos con relaciones
        $prestamos = Prestamo::whereIn('estado', ['Activo', 'Vencido'])
            ->with(['cliente', 'pagos', 'articulos'])
            ->get();

        // Procesar cada préstamo
        $procesados = $prestamos->map(function (Prestamo $p) use ($hoy) {
            $fechaVencimiento = $this->calcularFechaVencimiento($p);
            $diasEnvejecimiento = $this->calcularDiasEnvejecimiento($p, $hoy);
            $estadoEnvejecimiento = $this->determinarEstadoEnvejecimiento($diasEnvejecimiento);
            $mesesPagados = $p->pagos->where('tipo_pago', 'Interes')->count();

            $capitalPagado = $p->pagos->where('tipo_pago', 'Capital')->sum('monto_pagado');
            $saldoPendiente = max(0, $p->monto - $capitalPagado);

            return [
                'id'                    => $p->id,
                'codigo'                => $p->codigo,
                'cliente_nombre'        => $p->cliente->nombre ?? 'Desconocido',
                'cliente_id'            => $p->cliente_id,
                'monto'                 => $p->monto,
                'saldo_pendiente'       => $saldoPendiente,
                'fecha_prestamo'        => $p->fecha_prestamo,
                'fecha_vencimiento'     => $fechaVencimiento->toDateString(),
                'dias_envejecimiento'   => $diasEnvejecimiento,
                'estado_envejecimiento' => $estadoEnvejecimiento,
                'meses_pagados'         => $mesesPagados,
                'estado'                => $p->estado,
                'articulos'             => $p->articulos->map(fn($a) => [
                    'nombre' => $a->nombre_articulo,
                    'detalle' => $a->descripcion . ($a->marca ? ' - ' . $a->marca : ''),
                ]),
            ];
        });

        // Construir 4 bloques semanales con 7 días cada uno
        $bloques = [];
        for ($i = 0; $i < 4; $i++) {
            $inicio = $hoy->copy()->addDays($i * 7);
            $fin    = $hoy->copy()->addDays(($i * 7) + 6);

            // Generar los 7 días de la semana
            $dias = [];
            $totalSemana = 0;
            $countSemana = 0;

            for ($d = 0; $d < 7; $d++) {
                $fechaDia = $inicio->copy()->addDays($d);
                $fechaStr = $fechaDia->toDateString();

                $prestamosDia = $procesados->filter(function ($p) use ($fechaStr) {
                    return $p['fecha_vencimiento'] === $fechaStr
                        && $p['estado_envejecimiento'] !== 'remate';
                })->values();

                $totalSemana += $prestamosDia->sum('saldo_pendiente');
                $countSemana += $prestamosDia->count();

                $dias[] = [
                    'fecha'      => $fechaStr,
                    'nombre_dia' => mb_strtoupper($fechaDia->locale('es')->isoFormat('ddd')),
                    'numero_dia' => $fechaDia->day,
                    'es_hoy'     => $fechaDia->isSameDay($hoy),
                    'prestamos'  => $prestamosDia,
                ];
            }

            $bloques[] = [
                'semana'       => $i + 1,
                'rango'        => $inicio->translatedFormat('d M') . ' - ' . $fin->translatedFormat('d M'),
                'fecha_inicio' => $inicio->toDateString(),
                'fecha_fin'    => $fin->toDateString(),
                'dias'         => $dias,
                'resumen'      => [
                    'total'       => $countSemana,
                    'monto_total' => $totalSemana,
                ],
            ];
        }

        // Préstamos vencidos que NO caen en las 4 semanas (vencieron antes de hoy)
        // y tienen >0 días de envejecimiento — agrupados por nivel
        $vencidosAntes = $procesados->filter(function ($p) use ($hoy) {
            $fv = Carbon::parse($p['fecha_vencimiento']);
            return $fv->lt($hoy) && $p['dias_envejecimiento'] > 0;
        })->sortByDesc('dias_envejecimiento')->values();

        // Lista de recuperación (>120 días — remate)
        $listaRecuperacion = $procesados->filter(fn($p) => $p['estado_envejecimiento'] === 'remate')
            ->sortByDesc('dias_envejecimiento')
            ->values();

        // KPIs
        $kpis = [
            'total_por_cobrar'     => $procesados->sum('saldo_pendiente'),
            'vencimientos_semana'  => $bloques[0]['resumen']['total'] ?? 0,
            'en_riesgo'            => $procesados->whereIn('estado_envejecimiento', ['rojo', 'azul'])->count(),
            'en_remate'            => $listaRecuperacion->count(),
            'total_prestamos'      => $procesados->count(),
        ];

        // Contadores por nivel
        $contadoresPorNivel = [];
        foreach (self::AGING_LEVELS as $key => $level) {
            $contadoresPorNivel[$key] = [
                'count' => $procesados->where('estado_envejecimiento', $key)->count(),
                'monto' => $procesados->where('estado_envejecimiento', $key)->sum('saldo_pendiente'),
                'label' => $level['label'],
                'emoji' => $level['emoji'],
            ];
        }

        return [
            'bloquesSemana'      => $bloques,
            'vencidosAntes'      => $vencidosAntes,
            'listaRecuperacion'  => $listaRecuperacion,
            'kpis'               => $kpis,
            'contadores'         => $contadoresPorNivel,
            'fechaHoy'           => $hoy->toDateString(),
        ];
    }
}
