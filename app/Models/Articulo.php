<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Articulo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'prestamo_id',
        'nombre_articulo',
        'descripcion',
        'estado',
        'foto_url',
    ];

    public function prestamo()
    {
        return $this->belongsTo(Prestamo::class);
    }
}

