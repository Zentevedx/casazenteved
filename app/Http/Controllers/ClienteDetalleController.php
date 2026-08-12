<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Services\AgingService;
use Illuminate\Http\Request;

class ClienteDetalleController extends Controller
{
    public function __construct(
        private AgingService $agingService
    ) {}

    public function show(Cliente $cliente)
    {
        $cliente->load('prestamos.pagos', 'prestamos.articulos');

        $resumenFinanciero = $this->calcularResumenFinanciero($cliente);

        // Agregar datos de aging a cada préstamo (fuente única)
        $prestamosConAging = $cliente->prestamos->map(function ($prestamo) {
            $aging = $this->agingService->getAgingData($prestamo);
            $prestamoArray = $prestamo->toArray();
            $prestamoArray['aging'] = $aging;
            $prestamoArray['retraso_texto'] = $this->formatearRetraso($aging);
            return $prestamoArray;
        })->values();

        return inertia('Clientes/Detalle', [
            'cliente' => $cliente,
            'prestamos' => $prestamosConAging,
            'resumenFinanciero' => $resumenFinanciero,
        ]);
    }

    /**
     * Formatear el texto de retraso para mostrar al usuario.
     */
    private function formatearRetraso(array $aging): ?string
    {
        if ($aging['dias_retraso'] <= 0) return null;
        
        $dias = $aging['dias_retraso'];
        $meses = floor($aging['meses_retraso']);
        $diasRestantes = $dias - ($meses * 30);
        
        $partes = [];
        if ($meses > 0) $partes[] = "{$meses} " . ($meses === 1 ? 'mes' : 'meses');
        if ($diasRestantes > 0) $partes[] = "{$diasRestantes} " . ($diasRestantes === 1 ? 'día' : 'días');
        
        return count($partes) > 0 ? implode(' y ', $partes) . ' de retraso' : 'Vence hoy';
    }

    /**
     * Calcula totales financieros reales del cliente
     */
    private function calcularResumenFinanciero(Cliente $cliente): array
    {
        $prestamos = $cliente->prestamos;

        $totalPrestamos = $prestamos->sum('monto');

        $todosLosPagos = $prestamos->flatMap(fn($p) => $p->pagos);

        $totalIntereses = $todosLosPagos
            ->where('tipo_pago', 'Interes')
            ->sum('monto_pagado');

        $totalPagosCapital = $todosLosPagos
            ->where('tipo_pago', 'Capital')
            ->sum('monto_pagado');

        // Deuda pendiente = monto de préstamos activos/vencidos - pagos de capital de esos préstamos
        $prestamosActivos = $prestamos->whereIn('estado', ['Activo', 'Vencido']);
        $montoActivos = $prestamosActivos->sum('monto');
        $capitalPagadoActivos = $prestamosActivos
            ->flatMap(fn($p) => $p->pagos)
            ->where('tipo_pago', 'Capital')
            ->sum('monto_pagado');

        $deudaPendiente = max(0, $montoActivos - $capitalPagadoActivos);

        return [
            'total_prestamos' => $totalPrestamos,
            'total_intereses' => $totalIntereses,
            'total_pagos_capital' => $totalPagosCapital,
            'deuda_pendiente' => $deudaPendiente,
        ];
    }
}

