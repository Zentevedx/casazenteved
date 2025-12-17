<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Prestamo;
use App\Models\Articulo;

class BusquedaGlobalController extends Controller
{
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
                'titulo' => $c->nombre,
                'subtitulo' => "CI: {$c->ci}",
                'tipo' => 'Cliente',
                'icono' => '👤',
                'url' => route('clientes.detalle', $c->id)
            ]);

        // 2. Préstamos
        $prestamos = Prestamo::where('codigo', 'like', "%{$query}%")
            ->with('cliente')
            ->limit(5)
            ->get()
            ->map(fn($p) => [
                'titulo' => "Préstamo #{$p->codigo}",
                'subtitulo' => $p->cliente ? "Cliente: {$p->cliente->nombre}" : "Sin cliente",
                'tipo' => 'Préstamo',
                'icono' => '📄',
                'url' => $p->cliente_id ? route('clientes.detalle', $p->cliente_id) : '#'
            ]);

        // 3. Artículos (NUEVO: Busca por nombre de la prenda)
        $articulos = Articulo::where('nombre_articulo', 'like', "%{$query}%")
            ->with('prestamo.cliente')
            ->limit(5)
            ->get()
            ->map(fn($a) => [
                'titulo' => $a->nombre_articulo,
                'subtitulo' => $a->prestamo && $a->prestamo->cliente 
                                ? "De: {$a->prestamo->cliente->nombre}" 
                                : "En inventario",
                'tipo' => 'Artículo',
                'icono' => '📦',
                // Al hacer clic, te lleva al detalle del cliente dueño del préstamo
                'url' => ($a->prestamo && $a->prestamo->cliente_id) 
                            ? route('clientes.detalle', $a->prestamo->cliente_id) 
                            : '#'
            ]);

        return response()->json($clientes->merge($prestamos)->merge($articulos));
    }
}