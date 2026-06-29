<?php

namespace App\Services;

use App\Models\HorarioTecnico;
use App\Models\Reserva;
use App\Models\Servicio;
use App\Models\Tecnico;
use Carbon\Carbon;

class AsignadorTecnico
{
    /**
     * Devuelve el primer técnico disponible para el servicio en la fecha/hora dada,
     * o null si ninguno puede atenderlo.
     */
    public static function encontrarDisponible(
        int $servicioId,
        string $fecha,
        string $hora
    ): ?Tecnico {
        $servicio = Servicio::findOrFail($servicioId);
        $inicio   = Carbon::parse("{$fecha} {$hora}");
        $fin      = $inicio->copy()->addMinutes($servicio->duracion_minutos);
        $diaSemana = (int) $inicio->dayOfWeek;

        $tecnicosConHorario = HorarioTecnico::where('dia_semana', $diaSemana)
            ->where('hora_inicio', '<=', $inicio->format('H:i:s'))
            ->where('hora_fin', '>=', $fin->format('H:i:s'))
            ->with('tecnico')
            ->get()
            ->pluck('tecnico')
            ->filter();

        foreach ($tecnicosConHorario as $tecnico) {
            if (!self::tieneConflicto($tecnico->id, $fecha, $inicio, $fin)) {
                return $tecnico;
            }
        }

        return null;
    }

    /**
     * Verifica si el técnico ya tiene una reserva activa que se solapa con el rango dado.
     */
    private static function tieneConflicto(
        int $tecnicoId,
        string $fecha,
        Carbon $inicio,
        Carbon $fin
    ): bool {
        $reservas = Reserva::with('servicio')
            ->where('tecnico_id', $tecnicoId)
            ->whereDate('fecha', $fecha)
            ->where('estado', '!=', Reserva::ESTADO_CANCELADA)
            ->get();

        foreach ($reservas as $reserva) {
            $horaStr = substr((string) $reserva->hora, 0, 8);
            if (strlen($horaStr) === 5) {
                $horaStr .= ':00';
            }
            $fechaStr      = $reserva->fecha instanceof \Carbon\Carbon
                ? $reserva->fecha->format('Y-m-d')
                : (string) $reserva->fecha;
            $reservaInicio = Carbon::parse("{$fechaStr} {$horaStr}");
            $reservaFin    = $reservaInicio->copy()->addMinutes((int) $reserva->servicio->duracion_minutos);

            if ($inicio->lt($reservaFin) && $fin->gt($reservaInicio)) {
                return true;
            }
        }

        return false;
    }
}
