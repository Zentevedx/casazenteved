<?php
namespace App\Http\Controllers;

use App\Models\Prestamo;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
public function index()
{
    $fechaInicio = Carbon::now()->startOfDay();
    $fechaFin = Carbon::now()->addDays(30)->endOfDay();

    // CAMBIO 1: Añadimos 'articulos' a la carga de relaciones (eager loading)
    $prestamosActivos = Prestamo::with(['cliente', 'pagos', 'articulos'])
        ->where('estado', 'Activo')
        ->get();

    $prestamosProcesados = [];
    foreach ($prestamosActivos as $prestamo) {
        $pagosInteres = $prestamo->pagos->where('tipo_pago', 'Interes')->count();
        $fechaBase = Carbon::parse($prestamo->fecha_prestamo);
        $fechaReferencia = $fechaBase->copy()->addMonths($pagosInteres);
        $fechaProximoPago = $fechaReferencia->copy()->addMonth();

        $diasAntiguedad = now()->diffInDaysFiltered(fn(Carbon $date) => !$date->isWeekend(), $fechaReferencia);
        
        $color = '';
        if ($diasAntiguedad < 30) $color = 'verde';
        elseif ($diasAntiguedad < 60) $color = 'naranja';
        elseif ($diasAntiguedad < 90) $color = 'rojo';
        else continue;

        if ($fechaProximoPago->between($fechaInicio, $fechaFin)) {
            // CAMBIO 2: Añadimos los nuevos datos al array que se envía al frontend
            $prestamosProcesados[] = [
                'id' => $prestamo->id,
                'codigo' => $prestamo->codigo,
                'monto' => $prestamo->monto,
                'cliente_nombre' => $prestamo->cliente->nombre,
                'fecha_proximo_pago' => $fechaProximoPago->toDateString(),
                'color' => $color,
                'fecha_prestamo_original' => Carbon::parse($prestamo->fecha_prestamo)->toDateString(), // Fecha de inicio
                'articulos' => $prestamo->articulos, // Lista de artículos
                'pagos' => $prestamo->pagos->sortByDesc('fecha_pago'), // Lista de pagos ordenados
            ];
        }
    }

    $reporteFinal = collect($prestamosProcesados)
        ->groupBy('fecha_proximo_pago')
        ->map(fn ($prestamos, $fecha) => ['fecha' => $fecha, 'prestamos' => $prestamos])
        ->sortBy('fecha')
        ->values();

    return Inertia::render('Dashboard', [
        'reporte' => $reporteFinal,
        'fechaMostrada' => [
            'titulo' => 'Flujo de Pagos',
            'subtitulo' => $fechaInicio->translatedFormat('d M') . ' - ' . $fechaFin->translatedFormat('d M'),
        ],
    ]);
}
}