<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteDetalleController extends Controller
{
    public function show(Cliente $cliente)
    {
        $cliente->load('prestamos.pagos', 'prestamos.articulos');

        $resumenFinanciero = $this->calcularResumenFinanciero($cliente);

        return inertia('Clientes/Detalle', [
            'cliente' => $cliente,
            'prestamos' => $cliente->prestamos,
            'resumenFinanciero' => $resumenFinanciero,
        ]);
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

