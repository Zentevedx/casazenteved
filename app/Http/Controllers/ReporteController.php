<?php
namespace App\Http\Controllers;

use App\Models\Prestamo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ReporteController extends Controller
{
    public function reporteMensual($year, $month)
    {
        $fechaSolicitada = Carbon::create($year, $month, 1);
        $inicioMes = $fechaSolicitada->copy()->startOfMonth();
        $finMes = $fechaSolicitada->copy()->endOfMonth();

        $prestamosActivos = Prestamo::with(['cliente', 'pagos'])
            ->where('estado', 'Activo')
            ->get();

        $prestamosProcesados = [];

        foreach ($prestamosActivos as $prestamo) {
            // REGLA 1: Calcular la fecha de referencia sumando meses por cada pago de 'Interes'.
            $pagosInteres = $prestamo->pagos->where('tipo_pago', 'Interes')->count();
            $fechaBase = Carbon::parse($prestamo->fecha_prestamo);
            $fechaReferencia = $fechaBase->copy()->addMonths($pagosInteres);

            // La fecha del próximo pago es 1 mes después de la fecha de referencia.
            $fechaProximoPago = $fechaReferencia->copy()->addMonth();

            // REGLA 2: Calcular la antigüedad del ciclo de pago actual para el color.
            $diasAntiguedad = now()->diffInDaysFiltered(fn(Carbon $date) => !$date->isWeekend(), $fechaReferencia);
            
            $color = '';
            if ($diasAntiguedad < 30) {
                $color = 'verde';
            } elseif ($diasAntiguedad < 60) {
                $color = 'naranja';
            } elseif ($diasAntiguedad < 90) {
                $color = 'rojo';
            } else {
                // REGLA 3: Si tiene más de 90 días (aprox. 3 meses), se omite del reporte.
                continue;
            }

            // Solo incluimos el préstamo si su próximo pago es en el mes solicitado.
            if ($fechaProximoPago->between($inicioMes, $finMes)) {
                $prestamosProcesados[] = [
                    'id' => $prestamo->id,
                    'codigo' => $prestamo->codigo,
                    'monto' => $prestamo->monto,
                    'cliente_nombre' => $prestamo->cliente->nombre,
                    'fecha_proximo_pago' => $fechaProximoPago->toDateString(),
                    'color' => $color,
                ];
            }
        }
        
        // REGLA 4: Agrupar por semana del mes y luego por día de la semana.
        $reporteAgrupado = collect($prestamosProcesados)->groupBy([
            function ($item) {
                // Agrupamos por número de semana dentro del mes.
                return Carbon::parse($item['fecha_proximo_pago'])->weekOfMonth;
            },
            function ($item) {
                // Agrupamos por día de la semana (Lunes = 1, Domingo = 7).
                return Carbon::parse($item['fecha_proximo_pago'])->dayOfWeekIso;
            }
        ]);

        return Inertia::render('Reportes/Mensual', [
            'reporte' => $reporteAgrupado,
            'fechaMostrada' => [
                'year' => $fechaSolicitada->year,
                'month' => $fechaSolicitada->month,
                'monthName' => $fechaSolicitada->translatedFormat('F'),
            ],
        ]);
    }
}