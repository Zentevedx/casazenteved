<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prestamo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'cliente_id',
        'codigo',
        'monto',
        'fecha_prestamo',
        'estado',
        'multa_por_retraso',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function articulos()
    {
        return $this->hasMany(Articulo::class);
    }
    public function pagos()
{
    return $this->hasMany(\App\Models\Pago::class);
}

}
