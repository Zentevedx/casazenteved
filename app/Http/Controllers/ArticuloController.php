<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use Illuminate\Http\Request;

class ArticuloController extends Controller
{
    public function index()
    {
        $articulos = Articulo::latest()->paginate(20);
        return inertia('Articulos/Index', compact('articulos'));
    }
}
