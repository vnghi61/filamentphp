<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\ChartWidget;

class ProductChart extends ChartWidget
{
    protected static ?string $pollingInterval = '30s';
    
    public function getHeading(): string
    {
        return __('filament.product_statistics');
    }
    protected int | string | array $columnSpan = 'half';

    protected function getData(): array
    {
        $data = Product::with('category')
            ->get()
            ->groupBy('category.name')
            ->map->count()
            ->toArray();

        return [
            'datasets' => [
                [
                    'data' => array_values($data),
                    'backgroundColor' => ['#F59E0B', '#EF4444', '#8B5CF6', '#10B981', '#3B82F6'],
                ],
            ],
            'labels' => array_keys($data),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): ?array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'labels' => [
                        'font' => [
                            'size' => 10, // thu nhỏ font chữ
                        ],
                    ],
                ],
            ],
            'layout' => [
                'padding' => 20,
            ],
            'cutout' => '70%', // làm doughnut mỏng hơn
        ];
    }
    
    public function getHeight(): int | string | null
    {
        return 100; // giảm chiều cao
    }

    public static function getSort(): int
    {
        return 4;
    }
}