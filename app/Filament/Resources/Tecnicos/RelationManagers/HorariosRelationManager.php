<?php

namespace App\Filament\Resources\Tecnicos\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HorariosRelationManager extends RelationManager
{
    protected static string $relationship = 'horarios';

    protected static ?string $title = 'Horarios';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('dia_semana')
                ->label('Día de la semana')
                ->options([
                    0 => 'Domingo',
                    1 => 'Lunes',
                    2 => 'Martes',
                    3 => 'Miércoles',
                    4 => 'Jueves',
                    5 => 'Viernes',
                    6 => 'Sábado',
                ])
                ->required(),
            TimePicker::make('hora_inicio')
                ->label('Hora inicio')
                ->required()
                ->seconds(false),
            TimePicker::make('hora_fin')
                ->label('Hora fin')
                ->required()
                ->seconds(false),
        ]);
    }

    public function table(Table $table): Table
    {
        $dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

        return $table
            ->columns([
                TextColumn::make('dia_semana')
                    ->label('Día')
                    ->formatStateUsing(fn (int $state): string => $dias[$state] ?? $state),
                TextColumn::make('hora_inicio')
                    ->label('Inicio')
                    ->time('H:i'),
                TextColumn::make('hora_fin')
                    ->label('Fin')
                    ->time('H:i'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
