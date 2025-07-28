<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use \App\Traits\RedirectTraits;

class CreateProduct extends CreateRecord
{
    use RedirectTraits;

    protected static string $resource = ProductResource::class;

    public static function getnavigationLabel(): string
    {
        return __('filament.product_create');
    }

    public function getBreadcrumbs(): array
    {
        return [
            route('filament.admin.pages.dashboard') => __('filament.dashboard'),
            route('filament.admin.resources.products.index') => __('filament.product'),
            $this->getUrl() => __('filament.product'),
        ];
    }
}
