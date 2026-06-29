<?php

namespace App\Filament\Widgets;

use App\Models\Servicio;
use Filament\Widgets\ChartWidget;

class ReservasPorServicioWidget extends ChartWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Demanda por servicio';

    protected ?string $description = 'Total de reservas acumuladas por tipo de servicio';

    protected int | string | array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $servicios = Servicio::withCount('reservas')
            ->orderByDesc('reservas_count')
            ->get();

        $colores = [
            'rgba(99, 102, 241, 0.8)',
            'rgba(16, 185, 129, 0.8)',
            'rgba(245, 158, 11, 0.8)',
            'rgba(59, 130, 246, 0.8)',
            'rgba(239, 68, 68, 0.8)',
            'rgba(168, 85, 247, 0.8)',
        ];

        return [
            'datasets' => [
                [
                    'label'           => 'Reservas',
                    'data'            => $servicios->pluck('reservas_count')->toArray(),
                    'backgroundColor' => $servicios->keys()->map(fn ($i) => $colores[$i % count($colores)])->toArray(),
                    'borderRadius'    => 6,
                ],
            ],
            'labels' => $servicios->pluck('nombre')->toArray(),
        ];
    }
}
