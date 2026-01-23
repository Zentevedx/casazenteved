<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Articulo;

class FixArticuloStatusSeeder extends Seeder
{
    public function run()
    {
        $count = Articulo::whereHas('prestamo', function ($q) {
            $q->where('estado', 'Pagado');
        })->update(['estado' => 'Retirado']);
        
        $this->command->info("Se han actualizado $count articulos a estado Retirado.");
    }
}
