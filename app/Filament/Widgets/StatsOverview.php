<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\News;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';
    
    protected function getStats(): array
    {
        return [
            Stat::make(__('filament.user'), User::count())
                ->description(__('filament.total_users'))
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
                
            Stat::make(__('filament.order'), Order::count())
                ->description(__('filament.total_orders'))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),
                
            Stat::make(__('filament.product'), Product::count())
                ->description(__('filament.total_products'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('warning'),
                
            Stat::make(__('filament.news'), News::count())
                ->description(__('filament.total_news'))
                ->descriptionIcon('heroicon-m-newspaper')
                ->color('primary'),
        ];
    }

    public static function getSort(): int
    {
        return 1;
    }
}