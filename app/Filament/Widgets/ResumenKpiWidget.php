<?php

namespace App\Filament\Widgets;

use App\Models\Reserva;
use App\Models\Tecnico;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ResumenKpiWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $mes    = now()->month;
        $anio   = now()->year;
        $hoy    = today();

        $reservasHoy       = Reserva::whereDate('fecha', $hoy)->count();
        $pendientes        = Reserva::where('estado', Reserva::ESTADO_PENDIENTE)->count();
        $enProceso         = Reserva::where('estado', Reserva::ESTADO_EN_PROCESO)->count();
        $completadasMes    = Reserva::where('estado', Reserva::ESTADO_COMPLETADA)
                                ->whereMonth('fecha', $mes)
                                ->whereYear('fecha', $anio)
                                ->count();
        $totalClientes     = User::role('cliente')->count();
        $totalTecnicos     = Tecnico::count();

        // Mini-sparkline: reservas de los últimos 7 días
        $ultimos7 = collect(range(6, 0))->map(
            fn ($i) => Reserva::whereDate('fecha', today()->subDays($i))->count()
        )->toArray();

        return [
            Stat::make('Reservas hoy', $reservasHoy)
                ->description('Programadas para ' . $hoy->locale('es')->isoFormat('D MMM'))
                ->descriptionIcon('heroicon-o-calendar-days')
                ->descriptionColor('info')
                ->chart($ultimos7)
                ->chartColor('info')
                ->color('info'),

            Stat::make('Pendientes', $pendientes)
                ->description('Sin confirmar inicio')
                ->descriptionIcon('heroicon-o-clock')
                ->descriptionColor('warning')
                ->color('warning'),

            Stat::make('En proceso', $enProceso)
                ->description('En el taller ahora')
                ->descriptionIcon('heroicon-o-wrench-screwdriver')
                ->descriptionColor('info')
                ->color('info'),

            Stat::make('Completadas ' . now()->locale('es')->monthName, $completadasMes)
                ->description('Facturables este mes')
                ->descriptionIcon('heroicon-o-check-badge')
                ->descriptionColor('success')
                ->color('success'),

            Stat::make('Clientes', $totalClientes)
                ->description('Registrados en la plataforma')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('primary'),

            Stat::make('Técnicos', $totalTecnicos)
                ->description('Con horarios activos')
                ->descriptionIcon('heroicon-o-wrench')
                ->color('gray'),
        ];
    }
}
