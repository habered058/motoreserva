<?php

namespace App\Filament\Resources\Reservas\Tables;

use App\Models\Reserva;
use App\Models\Tecnico;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReservasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cliente.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tecnico.user.name')
                    ->label('Técnico')
                    ->searchable()
                    ->default('Sin asignar'),
                TextColumn::make('servicio.nombre')
                    ->label('Servicio')
                    ->searchable(),
                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('hora')
                    ->label('Hora')
                    ->time('H:i'),
                TextColumn::make('placa')
                    ->searchable(),
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
            ->filters([
                SelectFilter::make('estado')
                    ->options([
                        Reserva::ESTADO_PENDIENTE  => 'Pendiente',
                        Reserva::ESTADO_EN_PROCESO => 'En proceso',
                        Reserva::ESTADO_COMPLETADA => 'Completada',
                        Reserva::ESTADO_CANCELADA  => 'Cancelada',
                    ]),
                SelectFilter::make('tecnico_id')
                    ->label('Técnico')
                    ->options(fn () => Tecnico::with('user')->get()->pluck('user.name', 'id')),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('fecha', 'desc');
    }
}
