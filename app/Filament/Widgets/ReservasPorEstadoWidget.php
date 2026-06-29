<?php

namespace App\Filament\Widgets;

use App\Models\Reserva;
use Filament\Widgets\ChartWidget;

class ReservasPorEstadoWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Reservas por estado';

    protected ?string $description = 'Distribución actual de todas las reservas';

    protected int | string | array $columnSpan = 1;

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $counts = Reserva::query()
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->get()
            ->keyBy('estado');

        $estados = [
            Reserva::ESTADO_PENDIENTE  => ['Pendiente',   '#F59E0B'],
            Reserva::ESTADO_EN_PROCESO => ['En proceso',  '#3B82F6'],
            Reserva::ESTADO_COMPLETADA => ['Completada',  '#10B981'],
            Reserva::ESTADO_CANCELADA  => ['Cancelada',   '#EF4444'],
        ];

        $labels = [];
        $data   = [];
        $colors = [];

        foreach ($estados as $estado => [$label, $color]) {
            $labels[] = $label;
            $data[]   = $counts[$estado]->total ?? 0;
            $colors[] = $color;
        }

        return [
            'datasets' => [
                [
                    'data'            => $data,
                    'backgroundColor' => $colors,
                    'hoverOffset'     => 6,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
