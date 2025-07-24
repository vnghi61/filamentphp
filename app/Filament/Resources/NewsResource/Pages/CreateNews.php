<?php

namespace App\Filament\Resources\NewsResource\Pages;

use App\Filament\Resources\NewsResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use \App\Traits\RedirectTraits;

class CreateNews extends CreateRecord
{
    use RedirectTraits;

    protected static string $resource = NewsResource::class;
}
