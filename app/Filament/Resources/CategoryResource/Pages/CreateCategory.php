<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use \App\Traits\RedirectTraits;


class CreateCategory extends CreateRecord
{
    use RedirectTraits;

    protected static string $resource = CategoryResource::class;

    public static function getnavigationLabel(): string
    {
        return __('filament.category_create');
    }


    public function getBreadcrumbs(): array
    {
        return [
            route('filament.admin.pages.dashboard') => __('filament.dashboard'),
            route('filament.admin.resources.categories.index') => __('filament.category'),
            $this->getUrl() => __('filament.category_create'),
        ];
    }
}
