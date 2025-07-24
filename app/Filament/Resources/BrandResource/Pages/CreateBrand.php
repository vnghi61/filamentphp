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
}
