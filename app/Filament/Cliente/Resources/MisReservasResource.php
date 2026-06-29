<?php

namespace App\Filament\Cliente\Resources;

use App\Filament\Cliente\Resources\MisReservas\Pages\CreateMiReserva;
use App\Filament\Cliente\Resources\MisReservas\Pages\ListMisReservas;
use App\Models\Reserva;
use App\Models\Servicio;
use App\Models\Tecnico;
use App\Services\AsignadorTecnico;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MisReservasResource extends Resource
{
    protected static ?string $model = Reserva::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static ?string $navigationLabel = 'Mis Reservas';

    protected static ?string $modelLabel = 'Reserva';

    protected static ?string $pluralModelLabel = 'Mis Reservas';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('servicio_id')
                ->label('Servicio')
                ->options(Servicio::pluck('nombre', 'id'))
                ->required()
                ->live(),
            DatePicker::make('fecha')
                ->label('Fecha')
                ->required()
                ->minDate(today()->addDay()),
            TimePicker::make('hora')
                ->label('Hora')
                ->required()
                ->seconds(false),
            TextInput::make('marca_moto')
                ->label('Marca de la moto')
                ->required()
                ->maxLength(100),
            TextInput::make('modelo_moto')
                ->label('Modelo')
                ->required()
                ->maxLength(100),
            TextInput::make('placa')
                ->label('Placa')
                ->required()
                ->maxLength(10),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query) => $query->delCliente(auth()->id())->with(['servicio', 'tecnico.user'])
            )
            ->columns([
                TextColumn::make('servicio.nombre')->label('Servicio'),
                TextColumn::make('fecha')->label('Fecha')->date('d/m/Y')->sortable(),
                TextColumn::make('hora')->label('Hora')->time('H:i'),
                TextColumn::make('tecnico.user.name')->label('Técnico')->default('Por asignar'),
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
            ->recordActions([
                Action::make('cancelar')
                    ->label('Cancelar')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Reserva $record): bool => $record->estado === Reserva::ESTADO_PENDIENTE)
                    ->action(function (Reserva $record): void {
                        $record->update(['estado' => Reserva::ESTADO_CANCELADA]);
                        Notification::make()
                            ->title('Reserva cancelada')
                            ->success()
                            ->send();
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
            'index'  => ListMisReservas::route('/'),
            'create' => CreateMiReserva::route('/create'),
        ];
    }
}
