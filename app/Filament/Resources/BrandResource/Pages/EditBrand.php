<?php

namespace App\Filament\Resources\BrandResource\Pages;

use App\Filament\Resources\BrandResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use \App\Traits\RedirectTraits;

class EditBrand extends EditRecord
{
    use RedirectTraits;

    protected static string $resource = BrandResource::class;

    public function getTitle(): string
    {
        return __('filament.brand_edit');
    }

    public function getBreadcrumbs(): array
    {
        return [
            route('filament.admin.pages.dashboard') => __('filament.dashboard'),
            route('filament.admin.resources.brands.index') => __('filament.brand'),
        ];
    }
}
