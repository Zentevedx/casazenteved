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
        $sort = $request->input('sort', 'mas_recientes'); // Default sort

        // Configuración de Fechas
        $fechaHoy = Carbon::now()->startOfDay();
        $limiteAlDia = $fechaHoy->copy()->subDays(30); 
        $limiteMoraCritica = $fechaHoy->copy()->subMonths(3);

        // 1. Query Base Eager Loading eficiente
        $queryBase = Articulo::query()
            ->with(['prestamo.cliente', 'prestamo.pagos' => function($q) {
                $q->latest('fecha_pago');
            }])
            ->whereHas('prestamo', function (Builder $q) {
                // Excluir préstamos inactivos o finalizados
                $q->whereNotIn('estado', ['Pagado', 'Retirado', 'Cancelado', 'Adjudicado']);
            });

        // 2. Buscador Global en Artículos
        if ($search) {
            $queryBase->where(function($q) use ($search) {
                $q->where('nombre_articulo', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%")
                  ->orWhereHas('prestamo', function($p) use ($search) {
                      $p->where('codigo', 'like', "%{$search}%")
                        ->orWhereHas('cliente', fn($c) => $c->where('nombre', 'like', "%{$search}%"));
                  });
            });
        }

        // 3. Lógica de Filtros por Estado
        // Clonamos para contadores antes de aplicar filtros específicos
        $queryTodos = clone $queryBase;

        // Filtros específicos
        switch ($filtroEstado) {
            case 'aldia':
                $queryBase->whereHas('prestamo', function($q) use ($limiteAlDia) {
                    $q->where('estado', 'Pendiente')
                      ->whereDate('fecha_prestamo', '>=', $limiteAlDia);
                });
                break;

            case 'vencido':
                $queryBase->whereHas('prestamo', function($q) use ($limiteAlDia, $limiteMoraCritica) {
                    $q->where('estado', '!=', 'Pagado')
                      ->where(function($sub) use ($limiteAlDia, $limiteMoraCritica) {
                          $sub->whereDate('fecha_prestamo', '<', $limiteAlDia)
                              ->whereDate('fecha_prestamo', '>=', $limiteMoraCritica);
                      });
                });
                break;

            case 'mora_critica':
                $queryBase->whereHas('prestamo', function ($q) use ($limiteMoraCritica) {
                    $q->where(function ($subQ) use ($limiteMoraCritica) {
                        // Caso A: Sin pagos y fecha antigua
                        $subQ->whereDoesntHave('pagos')
                             ->whereDate('fecha_prestamo', '<', $limiteMoraCritica);
                    })->orWhere(function ($subQ) use ($limiteMoraCritica) {
                        // Caso B: Con pagos, pero el último es antiguo
                        $subQ->whereHas('pagos')
                             ->whereDoesntHave('pagos', fn($p) => $p->whereDate('fecha_pago', '>=', $limiteMoraCritica));
                    });
                });
                break;
        }

        // 4. KPIs y Contadores (Usando queries optimizadas)
        // Count de todos los activos
        $todosCount = (clone $queryTodos)->count();

        // Count de críticos (query independiente para el badge siempre visible)
        $moraCriticaCount = Articulo::whereHas('prestamo', function ($q) use ($limiteMoraCritica) {
            $q->whereNotIn('estado', ['Pagado', 'Retirado', 'Cancelado', 'Adjudicado'])
              ->where(function ($subQ) use ($limiteMoraCritica) {
                  $subQ->whereDoesntHave('pagos')->whereDate('fecha_prestamo', '<', $limiteMoraCritica);
              })->orWhere(function ($subQ) use ($limiteMoraCritica) {
                  $subQ->whereHas('pagos')->whereDoesntHave('pagos', fn($p) => $p->whereDate('fecha_pago', '>=', $limiteMoraCritica));
              });
        })->count();

        // Calcular Capital Visible en la vista actual
        // Obtenemos una colección ligera solo con los datos necesarios para sumar
        $articulosVisibles = (clone $queryBase)->with('prestamo')->get();
        $capitalVisible = $articulosVisibles->sum(function ($articulo) {
             if (!$articulo->prestamo) return 0;
             $count = $articulo->prestamo->articulos->count(); // Usar la relación cargada o count manual
             return $count > 0 ? $articulo->prestamo->monto / $count : 0;
        });


        // 5. Paginación y Transformación de Datos
        $articulos = $queryBase->join('prestamos', 'articulos.prestamo_id', '=', 'prestamos.id')
            ->select('articulos.*', 'prestamos.fecha_prestamo', 'prestamos.monto') // Select columns needed for ordering
            ->when($sort, function($q) use ($sort) {
                switch($sort) {
                    case 'mas_recientes':
                        return $q->orderBy('prestamos.fecha_prestamo', 'desc');
                    case 'mas_antiguos':
                        return $q->orderBy('prestamos.fecha_prestamo', 'asc');
                    case 'mayor_precio':
                        return $q->orderBy('prestamos.monto', 'desc');
                    case 'menor_precio':
                        return $q->orderBy('prestamos.monto', 'asc');
                    case 'criticos':
                        // Fallback sort: oldest loans first usually means more critical/late
                        return $q->orderBy('prestamos.fecha_prestamo', 'asc');
                }
            })
            ->paginate(16)
            ->withQueryString()
            ->through(function ($articulo) {
                $prestamo = $articulo->prestamo;
                $cantidadArticulos = $prestamo->articulos->count() ?: 1;
                
                // Determinar fecha de referencia (Inicio o Último Pago)
                $ultimoPago = $prestamo->pagos->first(); // Ya está ordenado por latest en la relación
                $fechaRef = $ultimoPago ? $ultimoPago->fecha_pago : $prestamo->fecha_prestamo;
                $origenFecha = $ultimoPago ? 'Último Pago' : 'Inicio Préstamo';

                // Cálculo de Mora
                $fechaRefCarbon = Carbon::parse($fechaRef)->startOfDay();
                $diasMora = intval($fechaRefCarbon->diffInDays(Carbon::now()->startOfDay(), false));

                // Formateo human-readable del tiempo
                $textoTiempo = $this->formatTimeText($diasMora);

                return [
                    'id' => $articulo->id,
                    'nombre' => $articulo->nombre_articulo,
                    'descripcion' => $articulo->descripcion,
                    'foto_url' => $articulo->foto_url,
                    'valor_proporcional' => $prestamo->monto / $cantidadArticulos,
                    'dias_mora' => $diasMora,
                    'tiempo_texto' => $textoTiempo,
                    'origen_calculo' => $origenFecha,
                    'es_critico' => $diasMora > 90,
                    'estado_prestamo' => $prestamo->estado,
                    'cliente' => [
                        'id' => $prestamo->cliente_id,
                        'nombre' => $prestamo->cliente->nombre . ' ' . $prestamo->cliente->apellido,
                    ],
                    'prestamo' => [
                        'id' => $prestamo->id,
                        'codigo' => $prestamo->codigo,
                        'monto_total' => $prestamo->monto,
                    ]
                ];
            });

        return Inertia::render('Articulos/Index', [
            'articulos' => $articulos,
            'filters' => ['estado' => $filtroEstado, 'search' => $search, 'sort' => $sort],
            'kpis' => [
                'capital_visible' => $capitalVisible,
                'items_visibles' => $articulos->total(),
            ],
            'counters' => [
                'todos' => $todosCount,
                'criticos' => $moraCriticaCount
            ]
        ]);
    }

    /**
     * Helper para formatear días en meses/días texto
     */
    private function formatTimeText($dias) {
        if ($dias < 30) return "$dias días";
        
        $meses = floor($dias / 30);
        $sobra = $dias % 30;
        
        $txtMes = $meses == 1 ? "1 mes" : "$meses meses";
        return $sobra > 0 ? "$txtMes y $sobra días" : $txtMes;
    }
}