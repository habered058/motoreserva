<?php

namespace App\Filament\Cliente\Resources\MisReservas\Pages;

use App\Filament\Cliente\Resources\MisReservasResource;
use App\Models\Reserva;
use App\Services\AsignadorTecnico;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateMiReserva extends CreateRecord
{
    protected static string $resource = MisReservasResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tecnico = AsignadorTecnico::encontrarDisponible(
            (int) $data['servicio_id'],
            $data['fecha'],
            $data['hora']
        );

        if ($tecnico === null) {
            throw ValidationException::withMessages([
                'hora' => 'No hay técnicos disponibles para el servicio, fecha y hora seleccionados. Por favor elige otro horario.',
            ]);
        }

        $data['cliente_id'] = auth()->id();
        $data['tecnico_id'] = $tecnico->id;
        $data['estado']     = Reserva::ESTADO_PENDIENTE;

        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Reserva creada exitosamente')
            ->body('Tu técnico ha sido asignado automáticamente.')
            ->success();
    }
}
