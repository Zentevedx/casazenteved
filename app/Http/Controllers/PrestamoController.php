<?php

namespace App\Http\Controllers;

use App\Models\Prestamo;
use App\Models\Articulo;
use App\Models\Caja;
use App\Models\Cliente;
use App\Services\PrestamoService;
use App\Services\AgingService;
use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;
use Inertia\Inertia;
use Carbon\Carbon;

class PrestamoController extends Controller
{
    public function __construct(protected PrestamoService $prestamoService)
    {
    }
    public function index(Request $request)
    {
        $search = $request->input('search');

        $prestamos = Prestamo::with('cliente')
            ->when($search, function ($query, $search) {
                $query->whereHas('cliente', function ($q) use ($search) {
                    $q->where('nombre', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->paginate(10)
            ->appends(['search' => $search]);

        return inertia('Prestamos/Index', [
            'prestamos' => $prestamos,
            'filters' => ['search' => $search],
        ]);
    }

    public function create()
    {
        $clientes = \App\Models\Cliente::all();
        return inertia('Prestamos/Create', compact('clientes'));
    }

    public function edit(Prestamo $prestamo)
    {
        $prestamo->load('articulos', 'cliente');
        $clientes = \App\Models\Cliente::all();

        return inertia('Prestamos/Edit', [
            'prestamo' => $prestamo,
            'clientes' => $clientes,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|unique:prestamos,codigo',
            'cliente_id' => 'required|exists:clientes,id',
            'monto' => 'required|numeric|min:0',
            'fecha_prestamo' => 'required|date',
            'multa_por_retraso' => 'required|numeric|min:0',
        ]);

        $datosPrestamo = $request->only(['codigo', 'cliente_id', 'monto', 'fecha_prestamo', 'multa_por_retraso']);
        
        $this->prestamoService->crearPrestamo($datosPrestamo, $request->articulos ?? []);

        return redirect()->route('prestamos.index')->with('success', 'Préstamo creado y dinero descontado de caja.');
    }

    public function update(Request $request, Prestamo $prestamo)
    {
        $request->validate([
            'codigo' => 'required|string|unique:prestamos,codigo,' . $prestamo->id,
            'cliente_id' => 'required|exists:clientes,id',
            'monto' => 'required|numeric|min:0',
            'fecha_prestamo' => 'required|date',
            'multa_por_retraso' => 'required|numeric|min:0',
        ]);

        $datosPrestamo = $request->only(['codigo', 'cliente_id', 'monto', 'fecha_prestamo', 'multa_por_retraso']);
        
        $this->prestamoService->actualizarPrestamo($prestamo, $datosPrestamo, $request->articulos ?? []);

        return redirect()->route('prestamos.index')->with('success', 'Préstamo actualizado correctamente.');
    }

    public function actualizarBasico(Request $request, Prestamo $prestamo)
    {
        $request->validate([
            'codigo' => 'required|string',
            'cliente_id' => 'required|exists:clientes,id',
            'monto' => 'required|numeric|min:0',
            'fecha_prestamo' => 'required|date',
            'multa_por_retraso' => 'required|numeric|min:0',
        ]);

        $prestamo->update([
            'codigo' => $request->codigo,
            'cliente_id' => $request->cliente_id,
            'monto' => $request->monto,
            'fecha_prestamo' => $request->fecha_prestamo,
            'multa_por_retraso' => $request->multa_por_retraso,
        ]);

        return redirect()->route('prestamos.index')->with('success', 'Préstamo actualizado correctamente.');
    }

    public function generarPdf(Prestamo $prestamo)
    {
        $prestamo->load('cliente', 'articulos');
        $html = view('pdf.boleta-prestamo', compact('prestamo'))->render();

        $base64 = Browsershot::html($html)
            ->showBackground()
            ->format('Letter')
            ->margins(0, 0, 0, 0)
            ->noSandbox()
            ->setChromePath('/usr/bin/chromium')
            ->base64pdf();

        return response(base64_decode($base64))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="boleta-' . $prestamo->codigo . '.pdf"');
    }

    public function updateEstado(Request $request, Prestamo $prestamo)
    {
        $request->validate([
            'estado' => 'required|in:Activo,Pagado,Vencido,Cancelado'
        ]);

        $this->prestamoService->actualizarEstado($prestamo, $request->estado);

        return back()->with('success', 'Estado del préstamo actualizado correctamente.');
    }

    /**
     * Vista de préstamos agrupados por rango de mora.
     * Rangos: 0-30, 31-60, 61-90, 90+ días de retraso.
     */
    public function porMora(Request $request, AgingService $agingService, ?string $rango = null)
    {
        $rango = $request->route('rango') ?? $rango ?? 'todas';
        
        $prestamos = Prestamo::with(['cliente', 'pagos', 'articulos'])
            ->whereIn('estado', ['Activo', 'Vencido'])
            ->get();
        
        // Procesar con AgingService
        $procesados = $prestamos->map(function ($p) use ($agingService) {
            $aging = $agingService->getAgingData($p);
            $p->aging = $aging;
            return $p;
        });

        // Filtrar por rango
        $filtrados = $procesados->filter(function ($p) use ($rango) {
            $dias = $p->aging['dias_retraso'];
            switch ($rango) {
                case '0-30':  return $dias >= 0 && $dias <= 30;
                case '31-60': return $dias >= 31 && $dias <= 60;
                case '61-90': return $dias >= 61 && $dias <= 90;
                case '90+':   return $dias > 90;
                case 'todas': return $dias > 0; // Solo las que tienen retraso
                default:      return true;
            }
        })->values();

        // Contadores por rango
        $conteos = [
            ['rango' => '0-30',  'label' => 'Al día',         'count' => $procesados->filter(fn($p) => $p->aging['dias_retraso'] >= 0 && $p->aging['dias_retraso'] <= 30)->count(), 'monto' => $procesados->filter(fn($p) => $p->aging['dias_retraso'] >= 0 && $p->aging['dias_retraso'] <= 30)->sum(fn($p) => $p->aging['saldo_pendiente'])],
            ['rango' => '31-60', 'label' => 'Riesgo Leve',    'count' => $procesados->filter(fn($p) => $p->aging['dias_retraso'] >= 31 && $p->aging['dias_retraso'] <= 60)->count(), 'monto' => $procesados->filter(fn($p) => $p->aging['dias_retraso'] >= 31 && $p->aging['dias_retraso'] <= 60)->sum(fn($p) => $p->aging['saldo_pendiente'])],
            ['rango' => '61-90', 'label' => 'Riesgo Alto',    'count' => $procesados->filter(fn($p) => $p->aging['dias_retraso'] >= 61 && $p->aging['dias_retraso'] <= 90)->count(), 'monto' => $procesados->filter(fn($p) => $p->aging['dias_retraso'] >= 61 && $p->aging['dias_retraso'] <= 90)->sum(fn($p) => $p->aging['saldo_pendiente'])],
            ['rango' => '90+',   'label' => 'En Remate',      'count' => $procesados->filter(fn($p) => $p->aging['dias_retraso'] > 90)->count(), 'monto' => $procesados->filter(fn($p) => $p->aging['dias_retraso'] > 90)->sum(fn($p) => $p->aging['saldo_pendiente'])],
        ];

        // KPIs
        $totalPendiente = $procesados->sum(fn($p) => $p->aging['saldo_pendiente']);
        $totalInteresesGenerados = $procesados->sum(fn($p) => $p->pagos->where('tipo_pago', 'Interes')->sum('monto_pagado'));
        $promedioRetraso = $filtrados->avg(fn($p) => $p->aging['dias_retraso']) ?? 0;

        return Inertia::render('Prestamos/PorMora', [
            'prestamos' => $filtrados,
            'conteos' => $conteos,
            'rangoActual' => $rango,
            'kpis' => [
                'total_pendiente' => $totalPendiente,
                'total_intereses' => $totalInteresesGenerados,
                'prestamos_en_mora' => $procesados->filter(fn($p) => $p->aging['dias_retraso'] > 30)->count(),
                'promedio_retraso' => round($promedioRetraso, 1),
                'cartera_total' => $procesados->sum('monto'),
            ],
        ]);
    }
}