<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Prestamo;
use App\Models\Articulo;

class BusquedaGlobalController extends Controller
{
    // Búsqueda AJAX en vivo
    public function ajax(Request $request)
    {
        $query = $request->input('q');

        if (!$query) return response()->json([]);

        // 1. Clientes
        $clientes = Cliente::where('nombre', 'like', "%{$query}%")
            ->orWhere('ci', 'like', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'titulo' => $c->nombre,
                'subtitulo' => "CI: {$c->ci}",
                'tipo' => 'Cliente', // Etiqueta para el frontend
                'icono' => '👤',
                'url' => route('clientes.detalle', $c->id)
            ]);

        // 2. Préstamos (Por código)
        $prestamos = Prestamo::where('codigo', 'like', "%{$query}%")
            ->with('cliente')
            ->limit(5)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'titulo' => "Préstamo #{$p->codigo}",
                'subtitulo' => $p->cliente ? "Cliente: {$p->cliente->nombre}" : "Sin cliente",
                'tipo' => 'Préstamo',
                'icono' => '📄',
                'url' => $p->cliente_id ? route('clientes.detalle', $p->cliente_id) : '#'
            ]);

        // 3. Artículos (Por nombre)
        $articulos = Articulo::where('nombre_articulo', 'like', "%{$query}%")
            ->with('prestamo.cliente')
            ->limit(5)
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'titulo' => $a->nombre_articulo,
                'subtitulo' => $a->prestamo && $a->prestamo->cliente 
                                ? "Cliente: {$a->prestamo->cliente->nombre}" 
                                : "En inventario",
                'tipo' => 'Artículo',
                'icono' => '📦',
                'url' => ($a->prestamo && $a->prestamo->cliente_id) 
                            ? route('clientes.detalle', $a->prestamo->cliente_id) 
                            : '#'
            ]);

        // Unimos todo en una sola colección
        return response()->json($clientes->merge($prestamos)->merge($articulos));
    }

    // Búsqueda tradicional (Enter) - Redirige al primer resultado
    public function buscar(Request $request)
    {
        $query = $request->input('q');
        
        // Prioridad: Cliente -> Préstamo -> Artículo
        $cliente = Cliente::where('nombre', 'like', "%{$query}%")->orWhere('ci', 'like', "%{$query}%")->first();
        if ($cliente) return redirect()->route('clientes.detalle', $cliente->id);

        $prestamo = Prestamo::where('codigo', 'like', "%{$query}%")->first();
        if ($prestamo) return redirect()->route('clientes.detalle', $prestamo->cliente_id);

        $articulo = Articulo::where('nombre_articulo', 'like', "%{$query}%")->with('prestamo')->first();
        if ($articulo && $articulo->prestamo) return redirect()->route('clientes.detalle', $articulo->prestamo->cliente_id);

        return back()->with('error', 'No se encontraron resultados.');
    }
}