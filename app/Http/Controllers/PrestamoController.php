<?php

namespace App\Http\Controllers;

use App\Models\Prestamo;
use App\Models\Articulo;
use App\Models\Caja; // <--- IMPORTANTE: Modelo Caja importado
use App\Models\Cliente;
use App\Services\PrestamoService;
use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;
use Inertia\Inertia;

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
}