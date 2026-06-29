<?php

namespace App\Filament\Resources\Servicios\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ServicioForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre')
                    ->required()
                    ->maxLength(255),
                TextInput::make('duracion_minutos')
                    ->label('Duración (minutos)')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                TextInput::make('precio')
                    ->label('Precio (COP)')
                    ->required()
                    ->numeric()
                    ->minValue(0),
            ]);
    }
}
