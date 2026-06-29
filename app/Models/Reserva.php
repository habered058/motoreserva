<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reserva extends Model
{
    const ESTADO_PENDIENTE  = 'pendiente';
    const ESTADO_EN_PROCESO = 'en_proceso';
    const ESTADO_COMPLETADA = 'completada';
    const ESTADO_CANCELADA  = 'cancelada';

    protected $fillable = [
        'cliente_id', 'tecnico_id', 'servicio_id',
        'fecha', 'hora', 'marca_moto', 'modelo_moto', 'placa', 'estado',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    public function tecnico(): BelongsTo
    {
        return $this->belongsTo(Tecnico::class);
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }

    public function scopeActivas($query)
    {
        return $query->where('estado', '!=', self::ESTADO_CANCELADA);
    }

    public function scopeDelCliente($query, int $clienteId)
    {
        return $query->where('cliente_id', $clienteId);
    }
}
