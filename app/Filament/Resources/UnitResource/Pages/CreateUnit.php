<?php

namespace App\Filament\Resources\UnitResource\Pages;

use App\Filament\Resources\UnitResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use \App\Traits\RedirectTraits;

class CreateUnit extends CreateRecord
{
    use RedirectTraits;

    protected static string $resource = UnitResource::class;
}
