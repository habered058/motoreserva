<?php

namespace App\Filament\Cliente\Resources\MisReservas\Pages;

use App\Filament\Cliente\Resources\MisReservasResource;
use App\Models\Reserva;
use App\Services\AsignadorTecnico;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;

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
            Notification::make()
                ->title('Sin disponibilidad')
                ->body('No hay técnicos disponibles para el servicio, fecha y hora seleccionados. Elige otro horario.')
                ->danger()
                ->send();

            throw new Halt();
        }

        $data['cliente_id'] = auth()->id();
        $data['tecnico_id'] = $tecnico->id;
        $data['estado']     = Reserva::ESTADO_PENDIENTE;

        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('¡Reserva creada!')
            ->body('Tu técnico ha sido asignado automáticamente.')
            ->success();
    }

    protected function getRedirectUrl(): string
    {
        return MisReservasResource::getUrl('index');
    }
}
