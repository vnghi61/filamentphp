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
}
