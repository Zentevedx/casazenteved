<?php

namespace App\Http\Controllers;

use App\Models\Prestamo;
use App\Models\Articulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Cliente;
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
        'multa_por_retraso' => 'required|numeric|min:0', // Multa por retraso
    ]);

    DB::transaction(function () use ($request) {
        $prestamo = Prestamo::create([
            'codigo' => $request->codigo,
            'cliente_id' => $request->cliente_id,
            'monto' => $request->monto,
            'fecha_prestamo' => $request->fecha_prestamo,
            'multa_por_retraso' => $request->multa_por_retraso, // Guardamos la multa por retraso
        ]);

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
    });

    return redirect()->route('prestamos.index')->with('success', 'Préstamo creado correctamente.');
}

public function update(Request $request, Prestamo $prestamo)
{
    // Validación de los campos
    $request->validate([
        'codigo' => 'required|string|unique:prestamos,codigo,' . $prestamo->id,
        'cliente_id' => 'required|exists:clientes,id',  // Asegurarse de que el cliente existe
        'monto' => 'required|numeric|min:0',
        'fecha_prestamo' => 'required|date',
        'multa_por_retraso' => 'required|numeric|min:0', // Multa por retraso
    ]);

    DB::transaction(function () use ($request, $prestamo) {
        // Actualizar los datos del préstamo
        $prestamo->update([
            'codigo' => $request->codigo,
            'cliente_id' => $request->cliente_id,
            'monto' => $request->monto,
            'fecha_prestamo' => $request->fecha_prestamo,
            'multa_por_retraso' => $request->multa_por_retraso, // Guardamos la multa por retraso
        ]);

        // Eliminar los artículos existentes antes de agregar los nuevos
        $prestamo->articulos()->delete();

        // Crear nuevos artículos
        foreach ($request->articulos as $articulo) {
            $fotoPath = null;
            if (isset($articulo['foto_url']) && is_file($articulo['foto_url'])) {
                // Guardar la foto del artículo en el almacenamiento
                $fotoPath = $articulo['foto_url']->store('articulos', 'public');
            } elseif (is_string($articulo['foto_url'])) {
                $fotoPath = $articulo['foto_url'];  // Si ya es una URL, la guardamos tal cual
            }

            // Crear el artículo asociado al préstamo
            $prestamo->articulos()->create([
                'nombre_articulo' => strtoupper($articulo['nombre_articulo']),
                'descripcion' => strtoupper($articulo['descripcion']),
                'foto_url' => $fotoPath,
            ]);
        }
    });

    // Redirigir a la lista de préstamos con mensaje de éxito
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

        $prestamo->update([
            'estado' => $request->estado
        ]);

        return back()->with('success', 'Estado del préstamo actualizado correctamente.');
    }
}
