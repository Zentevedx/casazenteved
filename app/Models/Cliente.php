<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'clientes';

    protected $fillable = [
        'nombre',
        'ci',
        'direccion',
        'telefono',
        'fecha_nacimiento',
    ];

    // Relación: Un cliente tiene muchos préstamos
    public function prestamos()
    {
        return $this->hasMany(Prestamo::class, 'cliente_id');
    }

    // Puedes agregar métodos de utilidad si deseas (opcional)
    public function nombreCI()
    {
        return $this->nombre . ' (' . $this->ci . ')';
    }
}
