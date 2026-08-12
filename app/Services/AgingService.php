<?php

namespace App\Services;

use App\Models\Prestamo;
use Carbon\Carbon;

/**
 * Servicio unificado de Aging / Vencimiento.
 * 
 * LÓGICA DE NEGOCIO (Regla Única):
 *   Cada pago de INTERÉS extiende el préstamo 1 mes desde la fecha base.
 *   La FECHA DE VENCIMIENTO = fecha_prestamo + count(pagos_interest) + 1 mes
 *   
 *   Si hoy > fecha_vencimiento → hay retraso.
 *   Los días de retraso = hoy - fecha_vencimiento.
 *   
 *   El pago de CAPITAL no extiende el plazo, solo reduce la deuda.
 *   Si todo el capital está pagado, el préstamo está cerrado.
 */
class AgingService
{
    /**
     * Calcular la fecha de vencimiento actual del préstamo.
     * 
     * @param Prestamo $prestamo
     * @return Carbon
     */
    public function getCurrentDueDate(Prestamo $prestamo): Carbon
    {
        $fechaBase = Carbon::parse($prestamo->fecha_prestamo);
        $numInteresesPagados = $prestamo->pagos
            ->where('tipo_pago', 'Interes')
            ->count();

        // Cada interés pagado = 1 mes de extensión + 1 mes de gracia actual
        return $fechaBase->copy()->addMonths($numInteresesPagados + 1);
    }

    /**
     * Calcular la fecha de referencia del último movimiento de interés
     * (última fecha hasta la cual el cliente está al día).
     * 
     * @param Prestamo $prestamo
     * @return Carbon
     */
    public function getLastPaidUpDate(Prestamo $prestamo): Carbon
    {
        $fechaBase = Carbon::parse($prestamo->fecha_prestamo);
        $numInteresesPagados = $prestamo->pagos
            ->where('tipo_pago', 'Interes')
            ->count();

        // Última fecha hasta la que pagó = fecha_base + meses_pagados
        return $fechaBase->copy()->addMonths($numInteresesPagados);
    }

    /**
     * Obtener los días de retraso desde la fecha de vencimiento hasta hoy (o fecha de corte).
     * 
     * @param Prestamo $prestamo
     * @param Carbon|null $asOf Fecha de corte (hoy por defecto)
     * @return int
     */
    public function getDaysOverdue(Prestamo $prestamo, ?Carbon $asOf = null): int
    {
        // Si el préstamo está pagado o cancelado, no hay retraso
        if (in_array($prestamo->estado, ['Pagado', 'Cancelado'])) {
            return 0;
        }

        $dueDate = $this->getCurrentDueDate($prestamo);
        $asOf = $asOf ?? Carbon::today();

        if ($asOf->lte($dueDate)) {
            return 0; // Aún no vence
        }

        return (int) $dueDate->startOfDay()->diffInDays($asOf->copy()->startOfDay());
    }

    /**
     * Obtener los meses de retraso (valor decimal para mayor precisión).
     * 
     * @param Prestamo $prestamo
     * @param Carbon|null $asOf
     * @return float
     */
    public function getMonthsOverdue(Prestamo $prestamo, ?Carbon $asOf = null): float
    {
        if (in_array($prestamo->estado, ['Pagado', 'Cancelado'])) {
            return 0.0;
        }

        $dueDate = $this->getCurrentDueDate($prestamo);
        $asOf = $asOf ?? Carbon::today();

        if ($asOf->lte($dueDate)) {
            return 0.0;
        }

        return $dueDate->startOfDay()->floatDiffInMonths($asOf->copy()->startOfDay());
    }

    /**
     * Determinar la categoría de envejecimiento.
     * 
     * @param int $daysOverdue
     * @return string  'verde' | 'amarillo' | 'rojo' | 'remate'
     */
    public function determineAgingCategory(int $daysOverdue): string
    {
        if ($daysOverdue <= 0) return 'verde';
        if ($daysOverdue <= 30) return 'verde';
        if ($daysOverdue <= 60) return 'amarillo';
        if ($daysOverdue <= 90) return 'rojo';
        return 'remate';
    }

    /**
     * Obtener todos los datos de aging para un préstamo en un array plano.
     * 
     * @param Prestamo $prestamo
     * @param Carbon|null $asOf
     * @return array
     */
    public function getAgingData(Prestamo $prestamo, ?Carbon $asOf = null): array
    {
        $dueDate = $this->getCurrentDueDate($prestamo);
        $lastPaidUp = $this->getLastPaidUpDate($prestamo);
        $daysOverdue = $this->getDaysOverdue($prestamo, $asOf);
        $monthsOverdue = $this->getMonthsOverdue($prestamo, $asOf);
        $category = $this->determineAgingCategory($daysOverdue);

        $capitalPagado = $prestamo->pagos
            ->where('tipo_pago', 'Capital')
            ->sum('monto_pagado');
        $saldoPendiente = max(0, $prestamo->monto - $capitalPagado);

        $ultimoPago = $prestamo->pagos
            ->sortByDesc('fecha_pago')
            ->first();

        return [
            'fecha_vencimiento'   => $dueDate->toDateString(),
            'fecha_al_dia'        => $lastPaidUp->toDateString(),
            'dias_retraso'        => $daysOverdue,
            'meses_retraso'       => round($monthsOverdue, 1),
            'categoria_aging'     => $category,
            'saldo_pendiente'     => $saldoPendiente,
            'capital_pagado'      => $capitalPagado,
            'ultimo_pago_fecha'   => $ultimoPago ? $ultimoPago->fecha_pago : null,
            'ultimo_pago_monto'   => $ultimoPago ? $ultimoPago->monto_pagado : 0,
            'ultimo_pago_tipo'    => $ultimoPago ? $ultimoPago->tipo_pago : null,
        ];
    }

    /**
     * Verificar si el capital completo ha sido pagado.
     * 
     * @param Prestamo $prestamo
     * @return bool
     */
    public function isCapitalFullyPaid(Prestamo $prestamo): bool
    {
        $capitalPagado = $prestamo->pagos
            ->where('tipo_pago', 'Capital')
            ->sum('monto_pagado');

        return $capitalPagado >= ($prestamo->monto - 0.1);
    }

    /**
     * Obtener la categoría de envejecimiento con etiqueta y color.
     * 
     * @param string $category
     * @return array
     */
    public static function getCategoryInfo(string $category): array
    {
        $info = [
            'verde'    => ['label' => 'Al Día',           'color' => 'emerald', 'emoji' => '🟢', 'dias_min' => 0,  'dias_max' => 30],
            'amarillo' => ['label' => 'Riesgo Leve',      'color' => 'yellow',  'emoji' => '🟡', 'dias_min' => 31, 'dias_max' => 60],
            'rojo'     => ['label' => 'Riesgo Alto',      'color' => 'orange',  'emoji' => '🟠', 'dias_min' => 61, 'dias_max' => 90],
            'remate'   => ['label' => 'En Remate',        'color' => 'red',     'emoji' => '🔴', 'dias_min' => 91, 'dias_max' => 9999],
        ];
        return $info[$category] ?? $info['verde'];
    }

    /**
     * Array de todas las categorías con su info.
     * 
     * @return array
     */
    public static function getAllCategories(): array
    {
        return [
            'verde'    => ['label' => 'Al Día',           'color' => 'emerald', 'emoji' => '🟢', 'dias_min' => 0,  'dias_max' => 30],
            'amarillo' => ['label' => 'Riesgo Leve',      'color' => 'yellow',  'emoji' => '🟡', 'dias_min' => 31, 'dias_max' => 60],
            'rojo'     => ['label' => 'Riesgo Alto',      'color' => 'orange',  'emoji' => '🟠', 'dias_min' => 61, 'dias_max' => 90],
            'remate'   => ['label' => 'En Remate',        'color' => 'red',     'emoji' => '🔴', 'dias_min' => 91, 'dias_max' => 9999],
        ];
    }
}
