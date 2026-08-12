<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Inertia\Inertia;

class ArticuloController extends Controller
{
    public function index(Request $request)
    {
        $filtro = $request->input('estado', 'todos');
        $search = $request->input('search');
        $sort = $request->input('sort', 'mas_recientes');

        $allArticulos = Articulo::with(['prestamo.cliente', 'prestamo.pagos'])
            ->whereHas('prestamo', fn($q) => $q->whereIn('estado', ['Activo', 'Vencido']))
            ->when($search, fn($q) => $q->where(function($q2) use ($search) {
                $q2->where('nombre_articulo', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%")
                  ->orWhereHas('prestamo', fn($p) => $p->where('codigo', 'like', "%{$search}%")
                      ->orWhereHas('cliente', fn($c) => $c->where('nombre', 'like', "%{$search}%")));
            }))
            ->get();

        $hoy = Carbon::today();

        $procesados = $allArticulos->map(function ($articulo) use ($hoy) {
            $prestamo = $articulo->prestamo;
            if (!$prestamo) return null;

            // LÓGICA DE DÍAS:
            // 1. Base = fecha_prestamo
            // 2. Si tiene pagos de INTERÉS → +30 días de gracia
            // 3. 90 días desde fecha_prestamo = Vencido
            $fechaBase = Carbon::parse($prestamo->fecha_prestamo)->startOfDay();
            $tienePagosInteres = $prestamo->pagos->where('tipo_pago', 'Interes')->isNotEmpty();

            if ($tienePagosInteres) {
                $fechaReferencia = $fechaBase->copy()->addDays(30);
            } else {
                $fechaReferencia = $fechaBase;
            }

            $diasRetraso = (int) $fechaReferencia->diffInDays($hoy, false);
            if ($diasRetraso < 0) $diasRetraso = 0;

            $fechaVencimiento = $fechaBase->copy()->addDays(90)->toDateString();

            $cat = $this->determinarCategoria($diasRetraso);
            $cantArticulos = max(1, $prestamo->articulos->count());

            // Saldo pendiente real
            $capitalPagado = $prestamo->pagos
                ->where('tipo_pago', 'Capital')
                ->sum('monto_pagado');
            $saldoPendiente = max(0, $prestamo->monto - $capitalPagado);

            return [
                'id' => $articulo->id,
                'nombre' => $articulo->nombre_articulo,
                'descripcion' => $articulo->descripcion,
                'foto_url' => $articulo->foto_url,
                'valor_proporcional' => $prestamo->monto / $cantArticulos,
                'dias_retraso' => $diasRetraso,
                'categoria_articulo' => $cat['key'],
                'categoria_label' => $cat['label'],
                'categoria_color' => $cat['color'],
                'categoria_emoji' => $cat['emoji'],
                'es_critico' => $diasRetraso > 90,
                'fecha_vencimiento' => $fechaVencimiento,
                'saldo_pendiente' => $saldoPendiente,
                'estado_prestamo' => $prestamo->estado,
                'cliente' => ['id' => $prestamo->cliente_id, 'nombre' => $prestamo->cliente?->nombre . ' ' . $prestamo->cliente?->apellido],
                'prestamo' => ['id' => $prestamo->id, 'codigo' => $prestamo->codigo, 'monto_total' => $prestamo->monto],
            ];
        })->filter()->values();

        $filtrados = $filtro !== 'todos'
            ? $procesados->filter(fn($a) => $a['categoria_articulo'] === $filtro)->values()
            : $procesados;

        $ordenado = match($sort) {
            'mas_antiguos' => $filtrados->sortBy('id'),
            'mayor_valor' => $filtrados->sortByDesc('valor_proporcional'),
            'menor_valor' => $filtrados->sortBy('valor_proporcional'),
            'criticos' => $filtrados->sortByDesc('dias_retraso'),
            default => $filtrados->sortByDesc('id'),
        };
        $filtrados = $ordenado->values();

        $conteos = [
            'todos' => $procesados->count(),
            'activos' => $procesados->filter(fn($a) => $a['categoria_articulo'] === 'activos')->count(),
            'retraso' => $procesados->filter(fn($a) => $a['categoria_articulo'] === 'retraso')->count(),
            'riesgo' => $procesados->filter(fn($a) => $a['categoria_articulo'] === 'riesgo')->count(),
            'vencidos' => $procesados->filter(fn($a) => $a['categoria_articulo'] === 'vencidos')->count(),
        ];

        $capitalFiltrado = $filtrados->sum('saldo_pendiente');
        $currentPage = (int) $request->input('page', 1);
        $perPage = 24;
        $total = $filtrados->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $items = $filtrados->forPage($currentPage, $perPage)->values();

        $links = [];
        $url = $request->url() . '?' . http_build_query(array_merge($request->except('page'), ['page' => '']));
        $links[] = ['url' => $currentPage > 1 ? $url . ($currentPage - 1) : null, 'label' => '&laquo; Anterior', 'active' => false];
        for ($i = 1; $i <= $lastPage; $i++) $links[] = ['url' => $url . $i, 'label' => (string) $i, 'active' => $i === $currentPage];
        $links[] = ['url' => $currentPage < $lastPage ? $url . ($currentPage + 1) : null, 'label' => 'Siguiente &raquo;', 'active' => false];

        return Inertia::render('Articulos/Index', [
            'articulos' => ['data' => $items, 'current_page' => $currentPage, 'last_page' => $lastPage, 'total' => $total, 'per_page' => $perPage, 'links' => $links],
            'filters' => ['estado' => $filtro, 'search' => $search, 'sort' => $sort],
            'kpis' => ['capital_visible' => $capitalFiltrado, 'items_visibles' => $total, 'items_criticos' => $conteos['vencidos']],
            'conteos' => $conteos,
        ]);
    }

    private function determinarCategoria(int $diasRetraso): array
    {
        if ($diasRetraso <= 30) return ['key' => 'activos', 'label' => 'Activo',  'color' => 'emerald', 'emoji' => '🟢'];
        if ($diasRetraso <= 60) return ['key' => 'retraso', 'label' => 'Retraso', 'color' => 'yellow',  'emoji' => '🟡'];
        if ($diasRetraso <= 90) return ['key' => 'riesgo',  'label' => 'Riesgo',  'color' => 'orange',  'emoji' => '🟠'];
        return ['key' => 'vencidos', 'label' => 'Vencido', 'color' => 'red', 'emoji' => '🔴'];
    }
}