<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use \App\Traits\RedirectTraits;

class EditProduct extends EditRecord
{
    use RedirectTraits;

    protected static string $resource = ProductResource::class;

    public function getTitle(): string
    {
        return __('filament.product_edit');
    }

    public function getBreadcrumbs(): array
    {
        return [
            route('filament.admin.pages.dashboard') => __('filament.dashboard'),
            route('filament.admin.resources.products.index') => __('filament.product'),
        ];
    }
}
