<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use \App\Traits\RedirectTraits;

class EditCategory extends EditRecord
{
    use RedirectTraits;
    protected static string $resource = CategoryResource::class;

    public function getTitle(): string
    {
        return __('filament.category_edit');
    }

    public function getBreadcrumbs(): array
    {
        return [
            route('filament.admin.pages.dashboard') => __('filament.dashboard'),
            route('filament.admin.resources.categories.index') => __('filament.category'),
        ];
    }
}
