<?php

namespace App\Filament\Resources\Tecnicos;

use App\Filament\Resources\Tecnicos\Pages\CreateTecnico;
use App\Filament\Resources\Tecnicos\Pages\EditTecnico;
use App\Filament\Resources\Tecnicos\Pages\ListTecnicos;
use App\Filament\Resources\Tecnicos\RelationManagers\HorariosRelationManager;
use App\Filament\Resources\Tecnicos\Schemas\TecnicoForm;
use App\Filament\Resources\Tecnicos\Tables\TecnicosTable;
use App\Models\Tecnico;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TecnicoResource extends Resource
{
    protected static ?string $model = Tecnico::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'especialidad';

    public static function form(Schema $schema): Schema
    {
        return TecnicoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TecnicosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            HorariosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTecnicos::route('/'),
            'create' => CreateTecnico::route('/create'),
            'edit' => EditTecnico::route('/{record}/edit'),
        ];
    }
}
