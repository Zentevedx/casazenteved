<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GastoController extends Controller
{
    /**
     * Muestra el listado de gastos.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        // Filtramos solo los movimientos que son Gastos
        $gastos = Caja::where('origen', 'Gasto')
            ->when($search, function ($query, $search) {
                $query->where('descripcion', 'like', '%' . $search . '%');
            })
            ->latest('fecha') // Ordenar por fecha descendente
            ->paginate(10)
            ->appends(['search' => $search]);

        return Inertia::render('Gastos/Index', [
            'gastos' => $gastos,
            'filters' => ['search' => $search],
        ]);
    }

    /**
     * Muestra el formulario de creación.
     */
    public function create()
    {
        return Inertia::render('Gastos/Create');
    }

    /**
     * Guarda un nuevo gasto en la base de datos.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'descripcion' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0.1',
            'fecha' => 'required|date',
        ]);

        // Lógica simple para obtener el último saldo y restar el gasto
        // Nota: En sistemas de alta concurrencia, esto debería manejarse con transacciones/bloqueos
        $ultimoSaldo = Caja::latest('id')->value('saldo_caja') ?? 0;
        $nuevoSaldo = $ultimoSaldo - $validated['monto'];

        Caja::create([
            'tipo_movimiento' => 'Egreso',
            'origen' => 'Gasto',
            'descripcion' => $validated['descripcion'],
            'monto' => $validated['monto'],
            'fecha' => $validated['fecha'],
            'saldo_caja' => $nuevoSaldo,
            // referencia_id y referencia_tabla quedan nulos para gastos generales
        ]);

        return redirect()->route('gastos.index')->with('success', 'Gasto registrado correctamente.');
    }

    /**
     * Muestra el formulario de edición.
     */
    public function edit($id)
    {
        $gasto = Caja::where('origen', 'Gasto')->findOrFail($id);
        return Inertia::render('Gastos/Edit', [
            'gasto' => $gasto
        ]);
    }

    /**
     * Actualiza el gasto.
     */
    public function update(Request $request, $id)
    {
        $gasto = Caja::where('origen', 'Gasto')->findOrFail($id);

        $validated = $request->validate([
            'descripcion' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0.1',
            'fecha' => 'required|date',
        ]);

        // Nota: Actualizar el monto aquí no recalcula automáticamente todos los saldos futuros
        // para mantener la integridad histórica simple en este paso.
        $gasto->update([
            'descripcion' => $validated['descripcion'],
            'monto' => $validated['monto'],
            'fecha' => $validated['fecha'],
        ]);

        return redirect()->route('gastos.index')->with('success', 'Gasto actualizado correctamente.');
    }

    /**
     * Elimina el gasto (Soft Delete).
     */
    public function destroy($id)
    {
        $gasto = Caja::where('origen', 'Gasto')->findOrFail($id);
        $gasto->delete();

        return redirect()->route('gastos.index')->with('success', 'Gasto eliminado correctamente.');
    }
}