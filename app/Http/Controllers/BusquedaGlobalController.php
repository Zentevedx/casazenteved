<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Necesario para DB::raw si fuera requerido, pero usaremos whereRaw
use App\Models\Cliente;
use App\Models\Prestamo;
use App\Models\Articulo;

class BusquedaGlobalController extends Controller
{
    public function ajax(Request $request)
    {
        $input = $request->input('q');
        
        if (!$input) return response()->json([]);

        // Limpiamos la búsqueda (quitamos espacios extra) y la pasamos a minúsculas para comparar
        $query = trim($input);
        $queryLower = strtolower($query);

        // 1. Clientes (Búsqueda por Nombre o CI)
        $clientes = Cliente::where(function($q) use ($query) {
                $q->where('nombre', 'like', "%{$query}%")
                  ->orWhere('ci', 'like', "%{$query}%");
            })
            ->limit(5)
            ->get()
            ->map(fn($c) => [
                'titulo' => $c->nombre,
                'subtitulo' => "CI: {$c->ci}",
                'tipo' => 'Cliente',
                'url' => route('clientes.detalle', $c->id)
            ]);

        // 2. Préstamos (Búsqueda Inteligente: Código o Monto)
        // Usamos whereRaw para forzar la comparación en minúsculas y asegurar compatibilidad
        $prestamos = Prestamo::with('cliente')
            ->where(function($q) use ($query, $queryLower) {
                // Opción A: Coincide con el Código (ignorando mayúsculas/minúsculas)
                $q->whereRaw('LOWER(codigo) LIKE ?', ["%{$queryLower}%"])
                // Opción B: Coincide con el Monto
                  ->orWhere('monto', 'like', "%{$query}%");
            })
            ->limit(5)
            ->get()
            ->map(fn($p) => [
                // Formateamos el título para mostrar Código y Monto
                'titulo' => "Préstamo #{$p->codigo} - " . number_format($p->monto, 2),
                'subtitulo' => $p->cliente ? "Cliente: {$p->cliente->nombre}" : "Sin cliente asignado",
                'tipo' => 'Prestamo',
                'url' => $p->cliente_id ? route('clientes.detalle', $p->cliente_id) : '#'
            ]);

        // 3. Artículos (Por nombre)
        $articulos = Articulo::where('nombre_articulo', 'like', "%{$query}%")
            ->with('prestamo.cliente')
            ->limit(5)
            ->get()
            ->map(fn($a) => [
                'titulo' => $a->nombre_articulo,
                'subtitulo' => $a->prestamo && $a->prestamo->cliente 
                                ? "De: {$a->prestamo->cliente->nombre}" 
                                : "En inventario",
                'tipo' => 'Articulo',
                'url' => ($a->prestamo && $a->prestamo->cliente_id) 
                            ? route('clientes.detalle', $a->prestamo->cliente_id) 
                            : '#'
            ]);

        return response()->json($clientes->merge($prestamos)->merge($articulos));
    }
}