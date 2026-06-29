<?php

namespace App\Filament\Cliente\Resources;

use App\Models\Servicio;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CatalogoServiciosResource extends Resource
{
    protected static ?string $model = Servicio::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static ?string $navigationLabel = 'Catálogo de Servicios';

    protected static ?string $modelLabel = 'Servicio';

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
            ->columns([
                TextColumn::make('nombre')
                    ->label('Servicio')
                    ->searchable(),
                TextColumn::make('duracion_minutos')
                    ->label('Duración')
                    ->formatStateUsing(fn (int $state): string => "{$state} min"),
                TextColumn::make('precio')
                    ->label('Precio')
                    ->formatStateUsing(fn (int $state): string => '$ ' . number_format($state, 0, ',', '.')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Cliente\Resources\CatalogoServicios\Pages\ListCatalogoServicios::route('/'),
        ];
    }
}
