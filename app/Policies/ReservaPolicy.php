<?php

namespace App\Policies;

use App\Models\Reserva;
use App\Models\User;

class ReservaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'tecnico', 'cliente']);
    }

    public function view(User $user, Reserva $reserva): bool
    {
        return match (true) {
            $user->hasRole('admin')   => true,
            $user->hasRole('tecnico') => $reserva->tecnico?->user_id === $user->id,
            $user->hasRole('cliente') => $reserva->cliente_id === $user->id,
            default                   => false,
        };
    }

    public function create(User $user): bool
    {
        return $user->hasRole('cliente');
    }

    public function update(User $user, Reserva $reserva): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('tecnico')) {
            return $reserva->tecnico?->user_id === $user->id
                && in_array($reserva->estado, [Reserva::ESTADO_EN_PROCESO, Reserva::ESTADO_COMPLETADA], true);
        }

        // Cliente solo puede cancelar reservas propias en estado pendiente
        return $user->hasRole('cliente')
            && $reserva->cliente_id === $user->id
            && $reserva->estado === Reserva::ESTADO_PENDIENTE;
    }

    public function delete(User $user, Reserva $reserva): bool
    {
        return $user->hasRole('admin');
    }

    public function restore(User $user, Reserva $reserva): bool
    {
        return $user->hasRole('admin');
    }

    public function forceDelete(User $user, Reserva $reserva): bool
    {
        return $user->hasRole('admin');
    }
}
