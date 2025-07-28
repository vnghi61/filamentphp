<?php

namespace App\Filament\Resources\NewscategoryResource\Pages;

use App\Filament\Resources\NewscategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNewscategories extends ListRecords
{
    protected static string $resource = NewscategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label(__('filament.create')),
        ];
    }

    public function getTitle(): string
    {
        return __('filament.news_category_list');
    }
    public function getBreadcrumbs(): array
    {
        return [
            route('filament.admin.pages.dashboard') => __('filament.dashboard'),
            route('filament.admin.resources.newscategories.index') => __('filament.news_category'),
        ];
    }
}
