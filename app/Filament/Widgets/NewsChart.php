<?php

namespace App\Filament\Widgets;

use App\Models\News;
use Filament\Widgets\ChartWidget;

class NewsChart extends ChartWidget
{
    protected static ?string $pollingInterval = '30s';
    
    public function getHeading(): string
    {
        return __('filament.news_statistics');
    }
    protected int | string | array $columnSpan = 'half';

    protected function getData(): array
    {
        $data = News::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'News',
                    'data' => array_values($data),
                    'backgroundColor' => '#8B5CF6',
                ],
            ],
            'labels' => [__('filament.jan'), __('filament.feb'), __('filament.mar'), __('filament.apr'), __('filament.may'), __('filament.jun'), __('filament.jul'), __('filament.aug'), __('filament.sep'), __('filament.oct'), __('filament.nov'), __('filament.dec')],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
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
        return 5;
    }
}