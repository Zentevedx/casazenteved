<?php

namespace App\Http\Controllers;

use App\Services\VencimientoService;
use Illuminate\Http\JsonResponse;

class VencimientoController extends Controller
{
    public function __construct(
        private VencimientoService $vencimientoService
    ) {}

    /**
     * Retorna los datos del tablero de cobranza como JSON.
     * Se consume desde el Dashboard vía fetch/axios (no Inertia).
     */
    public function index(): JsonResponse
    {
        $datos = $this->vencimientoService->construirTablero();

        return response()->json($datos);
    }
}
