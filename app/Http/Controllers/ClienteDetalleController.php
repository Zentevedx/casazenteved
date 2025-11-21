<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteDetalleController extends Controller
{
    public function show(Cliente $cliente)
    {
        $cliente->load('prestamos.pagos', 'prestamos.articulos');

        return inertia('Clientes/Detalle', [
            'cliente' => $cliente,
            'prestamos' => $cliente->prestamos,
        ]);
    }
}
//                         
