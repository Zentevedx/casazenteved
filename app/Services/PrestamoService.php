<?php

namespace App\Services;

use App\Models\Prestamo;
use App\Models\Caja;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PrestamoService
{
    /**
     * Crea un nuevo préstamo, procesa sus artículos e impacta sobre la caja general.
     */
    public function crearPrestamo(array $datosPrestamo, array $articulos): Prestamo
    {
        return DB::transaction(function () use ($datosPrestamo, $articulos) {
            // Generar código único para el comprobante
            $codigoComprobante = 'PRE-' . strtoupper(Str::random(8));

            $prestamo = Prestamo::create(array_merge($datosPrestamo, [
                'codigo_comprobante' => $codigoComprobante,
            ]));

            $this->guardarArticulos($prestamo, $articulos);

            $this->registrarSalidaCaja($prestamo);

            return $prestamo;
        });
    }

    /**
     * Actualiza la información de un préstamo y reemplaza sus artículos en garantía.
     */
    public function actualizarPrestamo(Prestamo $prestamo, array $datosPrestamo, array $articulos): Prestamo
    {
        return DB::transaction(function () use ($prestamo, $datosPrestamo, $articulos) {
            $prestamo->update($datosPrestamo);

            // Eliminar y re-crear los artículos
            $prestamo->articulos()->delete();
            $this->guardarArticulos($prestamo, $articulos);

            return $prestamo;
        });
    }

    /**
     * Actualiza únicamente el estado del préstamo. Si está 'Pagado', libera los artículos.
     */
    public function actualizarEstado(Prestamo $prestamo, string $estado): void
    {
        DB::transaction(function () use ($prestamo, $estado) {
            $prestamo->update(['estado' => $estado]);

            if ($estado === 'Pagado') {
                $prestamo->articulos()->update(['estado' => 'Retirado']);
            }
        });
    }

    /**
     * Auxiliar interno para procesar y almacenar fotos/detalles de las prendas físicas.
     */
    protected function guardarArticulos(Prestamo $prestamo, array $articulos): void
    {
        foreach ($articulos as $articulo) {
            $fotoPath = null;
            if (isset($articulo['foto_url']) && is_file($articulo['foto_url'])) {
                $fotoPath = $articulo['foto_url']->store('articulos', 'public');
            } elseif (is_string($articulo['foto_url'] ?? null)) {
                $fotoPath = $articulo['foto_url'];
            }

            $prestamo->articulos()->create([
                'nombre_articulo' => strtoupper($articulo['nombre_articulo'] ?? ''),
                'descripcion' => strtoupper($articulo['descripcion'] ?? ''),
                'foto_url' => $fotoPath,
            ]);
        }
    }

    /**
     * Auxiliar interno para generar el comprobante de salida de dinero (Kardex).
     */
    protected function registrarSalidaCaja(Prestamo $prestamo): void
    {
        $ultimoSaldo = Caja::latest('id')->value('saldo_caja') ?? 0;

        Caja::create([
            'tipo_movimiento' => 'Egreso',
            'origen'          => 'Prestamo',
            'descripcion'     => "Préstamo otorgado: {$prestamo->codigo}",
            'monto'           => $prestamo->monto,
            'saldo_caja'      => $ultimoSaldo - $prestamo->monto,
            'fecha'           => $prestamo->fecha_prestamo,
            'referencia_id'   => $prestamo->id,
            'referencia_tabla'=> 'prestamos',
        ]);
    }
}
