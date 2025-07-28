<?php

namespace App\Filament\Resources\NewscategoryResource\Pages;

use App\Filament\Resources\NewscategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use \App\Traits\RedirectTraits;

class EditNewscategory extends EditRecord
{
    use RedirectTraits;

    protected static string $resource = NewscategoryResource::class;

    public function getTitle(): string
    {
        return __('filament.news_category_edit');
    }

    public function getBreadcrumbs(): array
    {
        return [
            route('filament.admin.pages.dashboard') => __('filament.dashboard'),
            route('filament.admin.resources.newscategories.index') => __('filament.news_category'),
        ];
    }
}
