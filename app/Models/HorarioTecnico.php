<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HorarioTecnico extends Model
{
    protected $fillable = ['tecnico_id', 'dia_semana', 'hora_inicio', 'hora_fin'];

    public function tecnico(): BelongsTo
    {
        return $this->belongsTo(Tecnico::class);
    }
}
