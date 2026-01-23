<?php

namespace App\Http\Controllers;

use App\Models\Prestamo;
use App\Models\Articulo;
use App\Models\Caja; // <--- IMPORTANTE: Modelo Caja importado
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;
use Illuminate\Support\Str;

class PrestamoController extends Controller
{
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

        DB::transaction(function () use ($request) {
            // 1. Crear el Préstamo
            $prestamo = Prestamo::create([
                'codigo' => $request->codigo,
                'cliente_id' => $request->cliente_id,
                'monto' => $request->monto,
                'fecha_prestamo' => $request->fecha_prestamo,
                'multa_por_retraso' => $request->multa_por_retraso,
            ]);

            // Guardar artículos
            foreach ($request->articulos as $articulo) {
                $fotoPath = null;
                if (isset($articulo['foto_url']) && is_file($articulo['foto_url'])) {
                    $fotoPath = $articulo['foto_url']->store('articulos', 'public');
                }

                $prestamo->articulos()->create([
                    'nombre_articulo' => strtoupper($articulo['nombre_articulo']),
                    'descripcion' => strtoupper($articulo['descripcion']),
                    'foto_url' => $fotoPath,
                ]);
            }

            // 2. AUTOMATIZACIÓN CAJA: Registrar Salida (Egreso)
            // Obtenemos el saldo actual para calcular el nuevo
            $ultimoSaldo = Caja::latest('id')->value('saldo_caja') ?? 0;

            Caja::create([
                'tipo_movimiento' => 'Egreso',
                'origen'          => 'Prestamo',
                'descripcion'     => "Préstamo otorgado: {$prestamo->codigo}",
                'monto'           => $prestamo->monto,
                'saldo_caja'      => $ultimoSaldo - $prestamo->monto, // Restamos el dinero
                'fecha'           => $prestamo->fecha_prestamo,
                'referencia_id'   => $prestamo->id,
                'referencia_tabla'=> 'prestamos',
            ]);
        });

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

        DB::transaction(function () use ($request, $prestamo) {
            // Nota: Si cambias el monto aquí, idealmente deberías ajustar la caja también.
            // Por simplicidad, esta actualización solo toca el préstamo y sus artículos.
            
            $prestamo->update([
                'codigo' => $request->codigo,
                'cliente_id' => $request->cliente_id,
                'monto' => $request->monto,
                'fecha_prestamo' => $request->fecha_prestamo,
                'multa_por_retraso' => $request->multa_por_retraso,
            ]);

            $prestamo->articulos()->delete();

            foreach ($request->articulos as $articulo) {
                $fotoPath = null;
                if (isset($articulo['foto_url']) && is_file($articulo['foto_url'])) {
                    $fotoPath = $articulo['foto_url']->store('articulos', 'public');
                } elseif (is_string($articulo['foto_url'])) {
                    $fotoPath = $articulo['foto_url'];
                }

                $prestamo->articulos()->create([
                    'nombre_articulo' => strtoupper($articulo['nombre_articulo']),
                    'descripcion' => strtoupper($articulo['descripcion']),
                    'foto_url' => $fotoPath,
                ]);
            }
        });

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
        $pdf = Pdf::loadView('pdf.boleta-prestamo', compact('prestamo'));

        return $pdf->stream('boleta-' . $prestamo->codigo . '.pdf');
    }

    public function updateEstado(Request $request, Prestamo $prestamo)
    {
        $request->validate([
            'estado' => 'required|in:Activo,Pagado,Vencido,Cancelado'
        ]);

        DB::transaction(function () use ($request, $prestamo) {
            $prestamo->update([
                'estado' => $request->estado
            ]);

            // Si el estado cambia a Pagado o RetiradoLogic, liberamos los articulos
            if ($request->estado === 'Pagado') {
                $prestamo->articulos()->update(['estado' => 'Retirado']);
            }
        });

        return back()->with('success', 'Estado del préstamo actualizado correctamente.');
    }
}