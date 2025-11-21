<?php

namespace App\Http\Controllers;

use App\Models\Prestamo;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Carbon\Carbon;

class SemanaClientesController extends Controller
{
    public function index()
    {
        $hoy = Carbon::today('America/La_Paz');

        $dias = collect(range(0, 27))->map(fn ($i) => [
            'fecha'  => $hoy->copy()->addDays($i)->toDateString(),
            'nombre' => Str::upper($hoy->copy()->addDays($i)->locale('es')->isoFormat('dddd')),
        ]);

        $prestamos = Prestamo::with(['pagos' => fn ($q) => $q->latest('fecha_pago')])
            ->select('id','cliente_id', 'codigo', 'monto', 'fecha_prestamo')
            ->where('estado','Activo')
            ->get()
            ->map(function ($p) {
                $p->fecha_base = $p->fecha_prestamo;
                return $p;
            });

        return Inertia::render('SemanaClientes', [
            'dias' => $dias,
            'prestamos' => $prestamos,
            'mesActual' => Str::upper(now()->locale('es')->isoFormat('MMMM')),
        ]);
    }
}
