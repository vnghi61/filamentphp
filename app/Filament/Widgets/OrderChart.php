<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class OrderChart extends ChartWidget
{
    protected static ?string $pollingInterval = '30s';
    
    public function getHeading(): string
    {
        return __('filament.order_statistics');
    }
    protected int | string | array $columnSpan = 'half';

    protected function getData(): array
    {
        $data = Order::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => array_values($data),
                    'backgroundColor' => '#3B82F6',
                ],
            ],
            'labels' => [__('filament.jan'), __('filament.feb'), __('filament.mar'), __('filament.apr'), __('filament.may'), __('filament.jun'), __('filament.jul'), __('filament.aug'), __('filament.sep'), __('filament.oct'), __('filament.nov'), __('filament.dec')],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'ticks' => [
                        'stepSize' => 1,
                        'maxTicksLimit' => 5,
                    ],
                ],
            ],
        ];
    }

    public static function getSort(): int
    {
        return 3;
    }
}