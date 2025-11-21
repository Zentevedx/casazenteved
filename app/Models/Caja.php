<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Caja extends Model
{
    use SoftDeletes;

    protected $table = 'cajas';

    protected $fillable = [
        'tipo_movimiento',     // 'Ingreso' o 'Egreso'
        'origen',              // puede ser 'Gasto', 'Pago', 'Otro'
        'descripcion',
        'monto',
        'saldo_caja',
        'fecha',
        'referencia_id',
        'referencia_tabla',
    ];

    protected $dates = [
        'fecha',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
