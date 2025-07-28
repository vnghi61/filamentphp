<?php

namespace App\Filament\Resources\NewscategoryResource\Pages;

use App\Filament\Resources\NewscategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use \App\Traits\RedirectTraits;

class CreateNewscategory extends CreateRecord
{
    use RedirectTraits;

    protected static string $resource = NewscategoryResource::class;

    public static function getnavigationLabel(): string
    {
        return __('filament.news_category_create');
    }


    public function getBreadcrumbs(): array
    {
        return [
            route('filament.admin.pages.dashboard') => __('filament.dashboard'),
            route('filament.admin.resources.newscategories.index') => __('filament.news_category'),
            $this->getUrl() => __('filament.news_category_create'),
        ];
    }
}
