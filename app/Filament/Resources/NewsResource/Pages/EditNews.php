<?php

namespace App\Filament\Resources\NewsResource\Pages;

use App\Filament\Resources\NewsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use \App\Traits\RedirectTraits;

class EditNews extends EditRecord
{
    use RedirectTraits;

    protected static string $resource = NewsResource::class;

    public function getTitle(): string
    {
        return __('filament.news_edit');
    }

    public function getBreadcrumbs(): array
    {
        return [
            route('filament.admin.pages.dashboard') => __('filament.dashboard'),
            route('filament.admin.resources.news.index') => __('filament.news'),
        ];
    }
}
