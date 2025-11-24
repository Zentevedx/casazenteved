<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


/*
|--------------------------------------------------------------------------
| Controllers
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\{
    ProfileController,
    ClienteController,
    PrestamoController,
    ArticuloController,
    ClienteDetalleController,
    BusquedaGlobalController,
    SemanaClientesController,
    PagoController,
};
    use App\Http\Controllers\EstadisticasController;
use App\Http\Controllers\ReporteController;

// routes/web.php
use App\Http\Controllers\DashboardController;

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');
/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'       => Route::has('login'),
        'canRegister'    => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion'     => PHP_VERSION,
    ]);
});



Route::middleware('auth')->group(function () {

    /* PERFIL */
    Route::get   ('/profile',  [ProfileController::class, 'edit'   ])->name('profile.edit');
    Route::patch ('/profile',  [ProfileController::class, 'update' ])->name('profile.update');
    Route::delete('/profile',  [ProfileController::class, 'destroy'])->name('profile.destroy');

// En routes/web.php


// ... tus otras rutas ...

// Rutas para edición avanzada (NUEVAS)
Route::put('/prestamos/{prestamo}/estado', [PrestamoController::class, 'updateEstado'])->name('prestamos.updateEstado');
Route::put('/pagos/{pago}', [PagoController::class, 'update'])->name('pagos.update');
Route::delete('/pagos/{pago}', [PagoController::class, 'destroy'])->name('pagos.destroy'); // Por si necesitas borrar un pago mal hecho

Route::get('/estadisticas', [EstadisticasController::class, 'index'])->name('estadisticas');

    /* CRUD PRINCIPALES */

    Route::put('/prestamos/{prestamo}/basico', [PrestamoController::class, 'actualizarBasico'])->name('prestamos.actualizarBasico');
 
    Route::resource('clientes',  ClienteController::class);
    Route::resource('prestamos', PrestamoController::class);
    Route::get('/prestamos/{prestamo}/pdf', [PrestamoController::class, 'generarPdf'])->name('prestamos.pdf');
    Route::resource('articulos', ArticuloController::class);

    /* VISTAS DETALLE & BÚSQUEDA */
    Route::get('/clientes/{cliente}/detalle', [ClienteDetalleController::class, 'show'])->name('clientes.detalle');
    Route::get('/buscar',         [BusquedaGlobalController::class, 'buscar'])->name('buscar.global');
    Route::get('/buscar-dinamico',[BusquedaGlobalController::class, 'ajax'  ])->name('buscar.ajax');

    Route::post('/pagos', [PagoController::class, 'store'])->name('pagos.store');

    // routes/web.php
    Route::get('/semana-clientes', [SemanaClientesController::class, 'index'])->name('semana-clientes');

    /* SEMANA DE PRÉSTAMOS */
    Route::get('/dashboard/semana-prestamos', [SemanaClientesController::class, 'index'])
    ->name('dashboard.semana-prestamos');
    // routes/web.php

// Puedes mantener la ruta anterior o reemplazarla. Esta es la nueva:
Route::get('/reportes/mensual/{year}/{month}', [ReporteController::class, 'reporteMensual'])
    ->middleware(['auth', 'verified'])
    ->name('reportes.mensual');

});

/*
|--------------------------------------------------------------------------
| Auth scaffolding (Breeze o Jetstream)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
