<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Servicio extends Model
{
    protected $fillable = ['nombre', 'duracion_minutos', 'precio'];

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }
}
