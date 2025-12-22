<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Inertia\Inertia;

class ArticuloController extends Controller
{
    public function index(Request $request)
    {
        $filtroEstado = $request->input('estado', 'todos');
        $search = $request->input('search');

        // Configuración de Fechas
        $fechaHoy = Carbon::now()->startOfDay();
        $limiteAlDia = $fechaHoy->copy()->subDays(30); 
        $limiteMoraCritica = $fechaHoy->copy()->subMonths(3);

        // 1. Query Base con Pagos
        $queryBase = Articulo::with(['prestamo.cliente'])
            ->with(['prestamo' => function ($q) {
                $q->withCount('articulos')
                  ->with(['pagos' => function($p) {
                      $p->orderBy('fecha_pago', 'desc'); // Último pago primero
                  }]);
            }])
            ->whereHas('prestamo', function (Builder $q) {
                $q->whereNotIn('estado', ['Pagado', 'Retirado', 'Cancelado', 'Adjudicado']);
            });

        // 2. Buscador
        if ($search) {
            $queryBase->where(function($q) use ($search) {
                $q->where('nombre_articulo', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%")
                  ->orWhereHas('prestamo', fn($p) => $p->where('codigo', 'like', "%{$search}%"));
            });
        }

        // 3. Contadores
        $todosCount = (clone $queryBase)->count();
        $moraCriticaCount = Articulo::whereHas('prestamo', function ($q) use ($limiteMoraCritica) {
            $q->whereNotIn('estado', ['Pagado', 'Retirado', 'Cancelado', 'Adjudicado'])
              ->where(function ($subQ) use ($limiteMoraCritica) {
                  $subQ->whereDoesntHave('pagos')
                       ->whereDate('fecha_prestamo', '<', $limiteMoraCritica);
              })
              ->orWhere(function ($subQ) use ($limiteMoraCritica) {
                  $subQ->whereHas('pagos')
                       ->whereDoesntHave('pagos', fn($p) => $p->whereDate('fecha_pago', '>=', $limiteMoraCritica));
              });
        })->count();

        // 4. Filtros
        $query = clone $queryBase;
        switch ($filtroEstado) {
            case 'aldia':
                $query->whereHas('prestamo', fn($q) => 
                    $q->where('estado', 'Pendiente')
                      ->whereDate('fecha_prestamo', '>=', $limiteAlDia)
                );
                break;
            case 'vencido':
                $query->whereHas('prestamo', function($q) use ($limiteAlDia, $limiteMoraCritica) {
                    $q->where('estado', '!=', 'Pagado')
                      ->where(function($sub) use ($limiteAlDia, $limiteMoraCritica) {
                          $sub->whereDate('fecha_prestamo', '<', $limiteAlDia)
                              ->whereDate('fecha_prestamo', '>=', $limiteMoraCritica);
                      });
                });
                break;
            case 'mora_critica':
                $query->whereHas('prestamo', function ($q) use ($limiteMoraCritica) {
                    $q->where(function ($subQ) use ($limiteMoraCritica) {
                        $subQ->whereDoesntHave('pagos')->whereDate('fecha_prestamo', '<', $limiteMoraCritica);
                    })->orWhere(function ($subQ) use ($limiteMoraCritica) {
                        $subQ->whereHas('pagos')->whereDoesntHave('pagos', fn($p) => $p->whereDate('fecha_pago', '>=', $limiteMoraCritica));
                    });
                });
                break;
        }

        // 5. KPIs y Transformación
        $articulosParaStats = (clone $query)->get();
        $valorCapitalMostrado = $articulosParaStats->sum(function ($articulo) {
            return ($articulo->prestamo && $articulo->prestamo->articulos_count > 0)
                ? $articulo->prestamo->monto / $articulo->prestamo->articulos_count
                : 0;
        });

        $articulos = $query->join('prestamos', 'articulos.prestamo_id', '=', 'prestamos.id')
            ->select('articulos.*') 
            ->orderBy('prestamos.fecha_prestamo', 'asc')
            ->paginate(16)
            ->withQueryString()
            ->through(function ($articulo) {
                $prestamo = $articulo->prestamo;
                $cantidad = $prestamo->articulos_count > 0 ? $prestamo->articulos_count : 1;
                
                // --- LÓGICA DE FECHAS ---
                $fechaRef = $prestamo->fecha_prestamo;
                $origen = 'Inicio Préstamo';

                if ($prestamo->pagos && $prestamo->pagos->count() > 0) {
                    $ultimoPago = $prestamo->pagos->first();
                    $fechaRef = $ultimoPago->fecha_pago;
                    $origen = 'Último Pago';
                }

                $diasMora = intval(Carbon::parse($fechaRef)->startOfDay()->diffInDays(Carbon::now()->startOfDay(), false));

                // --- FORMATEO PULIDO (DÍAS -> MESES) ---
                if ($diasMora < 30) {
                    $textoTiempo = "$diasMora días";
                } else {
                    $meses = floor($diasMora / 30);
                    $diasSobra = $diasMora % 30;
                    $textoMes = $meses == 1 ? "1 mes" : "$meses meses";
                    
                    if ($diasSobra > 0) {
                        $textoTiempo = "$textoMes y $diasSobra días";
                    } else {
                        $textoTiempo = $textoMes;
                    }
                }

                return [
                    'id' => $articulo->id,
                    'nombre' => $articulo->nombre_articulo,
                    'descripcion' => $articulo->descripcion,
                    'foto_url' => $articulo->foto_url,
                    'valor_proporcional' => $prestamo->monto / $cantidad,
                    'dias_mora' => $diasMora,
                    'tiempo_texto' => $textoTiempo, // <--- CAMPO NUEVO FORMATEADO
                    'origen_calculo' => $origen,
                    'es_critico' => $diasMora > 90,
                    'cliente' => [
                        'id' => $prestamo->cliente_id,
                        'nombre' => $prestamo->cliente->nombre . ' ' . $prestamo->cliente->apellido,
                    ],
                    'prestamo' => [
                        'monto_total' => $prestamo->monto,
                    ]
                ];
            });

        return Inertia::render('Articulos/Index', [
            'articulos' => $articulos,
            'filters' => ['estado' => $filtroEstado, 'search' => $search],
            'kpis' => [
                'capital_visible' => $valorCapitalMostrado,
                'items_visibles' => $articulosParaStats->count(),
            ],
            'counters' => [
                'todos' => $todosCount,
                'criticos' => $moraCriticaCount
            ]
        ]);
    }
}