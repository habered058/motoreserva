<?php

namespace App\Filament\Widgets;

use App\Models\Reserva;
use Filament\Widgets\ChartWidget;

class ReservasPorDiaWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Reservas últimos 30 días';

    protected ?string $description = 'Cantidad de reservas programadas por día';

    protected int | string | array $columnSpan = 1;

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $reservasPorDia = Reserva::query()
            ->where('fecha', '>=', today()->subDays(29)->format('Y-m-d'))
            ->selectRaw('DATE(fecha) as dia, COUNT(*) as total')
            ->groupBy('dia')
            ->get()
            ->keyBy('dia');

        $labels = [];
        $data   = [];

        for ($i = 29; $i >= 0; $i--) {
            $fecha    = today()->subDays($i);
            $labels[] = $fecha->format('d/m');
            $data[]   = (int) ($reservasPorDia[$fecha->format('Y-m-d')]->total ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Reservas',
                    'data'            => $data,
                    'fill'            => true,
                    'backgroundColor' => 'rgba(99, 102, 241, 0.15)',
                    'borderColor'     => 'rgba(99, 102, 241, 1)',
                    'tension'         => 0.4,
                    'pointRadius'     => 3,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
