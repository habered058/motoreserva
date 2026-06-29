<?php

namespace App\Filament\Widgets;

use App\Models\Reserva;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class UltimasReservasWidget extends TableWidget
{
    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Últimas reservas')
            ->description('Las 8 reservas más recientes del sistema')
            ->query(
                Reserva::query()
                    ->with(['cliente', 'servicio', 'tecnico.user'])
                    ->latest('created_at')
                    ->limit(8)
            )
            ->columns([
                TextColumn::make('cliente.name')
                    ->label('Cliente')
                    ->searchable(),
                TextColumn::make('servicio.nombre')
                    ->label('Servicio'),
                TextColumn::make('tecnico.user.name')
                    ->label('Técnico')
                    ->default('—'),
                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('hora')
                    ->label('Hora')
                    ->time('H:i'),
                TextColumn::make('marca_moto')
                    ->label('Moto'),
                TextColumn::make('placa')
                    ->label('Placa'),
                TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Reserva::ESTADO_PENDIENTE  => 'warning',
                        Reserva::ESTADO_EN_PROCESO => 'info',
                        Reserva::ESTADO_COMPLETADA => 'success',
                        Reserva::ESTADO_CANCELADA  => 'danger',
                        default                    => 'gray',
                    }),
            ])
            ->paginated(false);
    }
}
