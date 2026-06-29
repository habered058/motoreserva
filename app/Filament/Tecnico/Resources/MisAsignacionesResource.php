<?php

namespace App\Filament\Tecnico\Resources;

use App\Filament\Tecnico\Resources\MisAsignaciones\Pages\ListMisAsignaciones;
use App\Models\Reserva;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MisAsignacionesResource extends Resource
{
    protected static ?string $model = Reserva::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Mis Asignaciones';

    protected static ?string $modelLabel = 'Reserva';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                $tecnico = auth()->user()->tecnico;

                return $query
                    ->where('tecnico_id', $tecnico?->id)
                    ->with(['servicio', 'cliente']);
            })
            ->columns([
                TextColumn::make('cliente.name')->label('Cliente')->searchable(),
                TextColumn::make('servicio.nombre')->label('Servicio'),
                TextColumn::make('fecha')->label('Fecha')->date('d/m/Y')->sortable(),
                TextColumn::make('hora')->label('Hora')->time('H:i'),
                TextColumn::make('marca_moto')->label('Moto'),
                TextColumn::make('placa')->label('Placa'),
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
            ])
            ->recordActions([
                Action::make('iniciar')
                    ->label('Iniciar')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (Reserva $record): bool => $record->estado === Reserva::ESTADO_PENDIENTE)
                    ->action(function (Reserva $record): void {
                        $record->update(['estado' => Reserva::ESTADO_EN_PROCESO]);
                        Notification::make()->title('Reserva iniciada')->info()->send();
                    }),
                Action::make('completar')
                    ->label('Completar')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Reserva $record): bool => $record->estado === Reserva::ESTADO_EN_PROCESO)
                    ->action(function (Reserva $record): void {
                        $record->update(['estado' => Reserva::ESTADO_COMPLETADA]);
                        Notification::make()->title('Reserva completada')->success()->send();
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMisAsignaciones::route('/'),
        ];
    }
}
