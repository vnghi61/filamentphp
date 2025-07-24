<?php

namespace App\Filament\Resources\NewscategoryResource\Pages;

use App\Filament\Resources\NewscategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use \App\Traits\RedirectTraits;

class CreateNewscategory extends CreateRecord
{
    use RedirectTraits;

    protected static string $resource = NewscategoryResource::class;
}
