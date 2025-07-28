<?php

namespace App\Filament\Resources\NewsResource\Pages;

use App\Filament\Resources\NewsResource;
use Filament\Resources\Pages\CreateRecord;
use \App\Traits\RedirectTraits;

class CreateNews extends CreateRecord
{
    use RedirectTraits;

    protected static string $resource = NewsResource::class;

    public static function getnavigationLabel(): string
    {
        return __('filament.news_create');
    }

    public function getBreadcrumbs(): array
    {
        return [
            route('filament.admin.pages.dashboard') => __('filament.dashboard'),
            route('filament.admin.resources.news.index') => __('filament.news'),
            $this->getUrl() => __('filament.news_create'),
        ];
    }
}
