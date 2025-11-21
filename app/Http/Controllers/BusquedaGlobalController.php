<?php

namespace App\Http\Controllers;


use App\Models\Cliente;
use App\Models\Prestamo;
use App\Models\Articulo;
use Illuminate\Http\Request;

class BusquedaGlobalController extends Controller
{
    public function buscar(Request $request)
    {
        $query = $request->input('q');

        // Buscar cliente
        $cliente = Cliente::where('nombre', 'like', "%{$query}%")
                          ->orWhere('ci', 'like', "%{$query}%")
                          ->first();
        if ($cliente) {
            return redirect()->route('clientes.detalle', $cliente->id);
        }

        // Buscar préstamo
        $prestamo = Prestamo::where('codigo', 'like', "%{$query}%")->first();
        if ($prestamo) {
            return redirect()->route('clientes.detalle', $prestamo->cliente_id);
        }

        // Buscar artículo
        $articulo = Articulo::where('nombre_articulo', 'like', "%{$query}%")->first();
        if ($articulo) {
            $prestamo = $articulo->prestamo;
            return redirect()->route('clientes.detalle', $prestamo->cliente_id);
        }

        return back()->with('error', 'No se encontraron resultados.');
    }
    public function ajax(Request $request)
{
    $query = $request->input('q');

    return response()->json([
        'clientes' => \App\Models\Cliente::where('nombre', 'like', "%{$query}%")
                            ->orWhere('ci', 'like', "%{$query}%")
                            ->limit(5)->get(),

        'prestamos' => \App\Models\Prestamo::where('codigo', 'like', "%{$query}%")
                            ->with('cliente')
                            ->limit(5)->get(),

        'articulos' => \App\Models\Articulo::where('nombre_articulo', 'like', "%{$query}%")
                            ->with('prestamo.cliente')
                            ->limit(500)->get(),
    ]);
}

}
