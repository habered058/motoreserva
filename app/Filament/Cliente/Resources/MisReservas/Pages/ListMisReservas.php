<?php

namespace App\Filament\Cliente\Resources\MisReservas\Pages;

use App\Filament\Cliente\Resources\MisReservasResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMisReservas extends ListRecords
{
    protected static string $resource = MisReservasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
