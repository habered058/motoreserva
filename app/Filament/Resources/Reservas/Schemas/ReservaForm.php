<?php

namespace App\Filament\Resources\Reservas\Schemas;

use App\Models\Reserva;
use App\Models\Tecnico;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class ReservaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('cliente_id')
                    ->relationship('cliente', 'name')
                    ->searchable()
                    ->required(),
                Select::make('tecnico_id')
                    ->label('Técnico asignado')
                    ->options(fn () => Tecnico::with('user')->get()->pluck('user.name', 'id'))
                    ->searchable()
                    ->nullable(),
                Select::make('servicio_id')
                    ->relationship('servicio', 'nombre')
                    ->required(),
                DatePicker::make('fecha')
                    ->required()
                    ->minDate(today()),
                TimePicker::make('hora')
                    ->required()
                    ->seconds(false),
                TextInput::make('marca_moto')
                    ->label('Marca')
                    ->required()
                    ->maxLength(100),
                TextInput::make('modelo_moto')
                    ->label('Modelo')
                    ->required()
                    ->maxLength(100),
                TextInput::make('placa')
                    ->required()
                    ->maxLength(10)
                    ->extraInputAttributes(['style' => 'text-transform:uppercase']),
                Select::make('estado')
                    ->options([
                        Reserva::ESTADO_PENDIENTE  => 'Pendiente',
                        Reserva::ESTADO_EN_PROCESO => 'En proceso',
                        Reserva::ESTADO_COMPLETADA => 'Completada',
                        Reserva::ESTADO_CANCELADA  => 'Cancelada',
                    ])
                    ->required()
                    ->default(Reserva::ESTADO_PENDIENTE),
            ]);
    }
}
