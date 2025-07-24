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
}
