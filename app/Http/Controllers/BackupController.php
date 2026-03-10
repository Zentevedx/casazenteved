<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class BackupController extends Controller
{
    public function create()
    {
        try {
            // Check for admin role again just to be safe (middleware handles it too)
            if (auth()->user()->role !== 'admin') {
                abort(403);
            }

            // Run the backup command
            // We use callSilent to avoid output buffer issues if any
            Artisan::call('backup:run', [
                '--only-db' => true,
                '--disable-notifications' => true,
            ]);

            $output = Artisan::output();
            Log::info("Backup output: " . $output);

            return back()->with('success', 'Respaldo de base de datos realizado con éxito. Archivo guardado en el servidor.');
        } catch (\Exception $e) {
            Log::error("Backup failed: " . $e->getMessage());
            return back()->with('error', 'Error al realizar el respaldo: ' . $e->getMessage());
        }
    }
}
