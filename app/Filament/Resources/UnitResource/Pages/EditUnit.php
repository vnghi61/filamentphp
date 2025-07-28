<?php

namespace App\Filament\Resources\UnitResource\Pages;

use App\Filament\Resources\UnitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use \App\Traits\RedirectTraits;

class EditUnit extends EditRecord
{
    use RedirectTraits;

    protected static string $resource = UnitResource::class;

    public function getTitle(): string
    {
        return __('filament.unit_edit');
    }

    public function getBreadcrumbs(): array
    {
        return [
            route('filament.admin.pages.dashboard') => __('filament.dashboard'),
            route('filament.admin.resources.units.index') => __('filament.unit'),
        ];
    }
}
