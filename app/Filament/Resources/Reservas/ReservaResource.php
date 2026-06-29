<?php

namespace App\Filament\Resources\Reservas;

use App\Filament\Resources\Reservas\Pages\EditReserva;
use App\Filament\Resources\Reservas\Pages\ListReservas;
use App\Filament\Resources\Reservas\Schemas\ReservaForm;
use App\Filament\Resources\Reservas\Tables\ReservasTable;
use App\Models\Reserva;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReservaResource extends Resource
{
    protected static ?string $model = Reserva::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'placa';

    public static function form(Schema $schema): Schema
    {
        return ReservaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReservasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReservas::route('/'),
            'edit' => EditReserva::route('/{record}/edit'),
        ];
    }
}
