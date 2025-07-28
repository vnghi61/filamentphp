<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrders extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';
    protected int | string | array $columnSpan = 'full';

    public function getHeading(): ?string
    {
        return null;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->latest()->limit(5))
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label(__('filament.invoice_number'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                ->label(__('filament.user')),
                Tables\Columns\TextColumn::make('total')
                    ->label(__('filament.total'))
                    ->money('VND'),
                Tables\Columns\TextColumn::make('order_status')
                    ->label(__('filament.order_status'))
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.created_at'))
                    ->dateTime('H:i:s d/m/Y'),
            ]);
    }

    public static function getSort(): int
    {
        return 6;
    }
}