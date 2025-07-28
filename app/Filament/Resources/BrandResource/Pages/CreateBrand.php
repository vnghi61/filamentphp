<?php

namespace App\Filament\Resources\BrandResource\Pages;

use App\Filament\Resources\BrandResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use \App\Traits\RedirectTraits;
class CreateBrand extends CreateRecord
{    
    use RedirectTraits;
    protected static string $resource = BrandResource::class;

    public static function getnavigationLabel(): string
    {
        return __('filament.brand_create');
    }


    public function getBreadcrumbs(): array
    {
        return [
            route('filament.admin.pages.dashboard') => __('filament.dashboard'),
            route('filament.admin.resources.brands.index') => __('filament.brand'),
            $this->getUrl() => __('filament.brand_create'),
        ];
    }

}
