<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteDetalleController extends Controller
{
    public function show(Cliente $cliente)
    {
        $cliente->load('prestamos.pagos', 'prestamos.articulos');

        // Calcular el desempeño del cliente (Scoring)
        $desempeno = $this->calcularDesempeno($cliente);

        return inertia('Clientes/Detalle', [
            'cliente' => $cliente,
            'prestamos' => $cliente->prestamos,
            'desempeno' => $desempeno,
        ]);
    }

    /**
     * Calcula una puntuación de 0 a 100 y métricas relevantes de desempeño
     */
    private function calcularDesempeno(Cliente $cliente): array
    {
        $prestamos = $cliente->prestamos;
        
        if ($prestamos->isEmpty()) {
            return [
                'score' => 0,
                'estrellas' => 0,
                'etiqueta' => 'Nuevo Cliente',
                'color' => 'gray',
                'metricas' => [
                    'total' => 0,
                    'pagados' => 0,
                    'con_multa' => 0,
                ]
            ];
        }

        $totalPrestamos = $prestamos->count();
        $pagados = $prestamos->where('estado', 'Pagado')->count();
        $conMulta = $prestamos->where('multa_por_retraso', '>', 0)->count();
        $activosVencidos = $prestamos->whereIn('estado', ['Activo', 'Vencido']);

        // Puntuación Base
        $score = 50; // Empezamos en un nivel medio

        // Bonificaciones (Experiencia y Cumplimiento)
        $score += ($pagados * 10); // +10 puntos por cada préstamo finalizado exitosamente
        if ($pagados > 0 && $conMulta === 0) {
            $score += 10; // Bono de historial impoluto
        }

        // Penalizaciones
        $score -= ($conMulta * 15); // -15 puntos por cada préstamo que tuvo multas
        $score -= ($activosVencidos->where('estado', 'Vencido')->count() * 25); // -25 por préstamos actualmente vencidos

        // Limitar entre 0 y 100
        $score = max(0, min(100, $score));

        // Determinar Estrellas, Etiqueta y Color
        if ($score >= 90) {
            $estrellas = 5;
            $etiqueta = 'Excelente';
            $color = 'emerald';
        } elseif ($score >= 70) {
            $estrellas = 4;
            $etiqueta = 'Buen Cliente';
            $color = 'blue';
        } elseif ($score >= 40) {
            $estrellas = 3;
            $etiqueta = 'Regular';
            $color = 'yellow';
        } elseif ($score >= 20) {
            $estrellas = 2;
            $etiqueta = 'Riesgo Medio';
            $color = 'orange';
        } else {
            $estrellas = 1;
            $etiqueta = 'Alto Riesgo';
            $color = 'red';
        }

        return [
            'score' => $score,
            'estrellas' => $estrellas,
            'etiqueta' => $etiqueta,
            'color' => $color,
            'metricas' => [
                'total' => $totalPrestamos,
                'pagados' => $pagados,
                'con_multa' => $conMulta,
            ]
        ];
    }
}
