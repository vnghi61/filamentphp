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

    public static function getnavigationLabel(): string
    {
        return __('filament.unit_create');
    }

    public function getBreadcrumbs(): array
    {
        return [
            route('filament.admin.pages.dashboard') => __('filament.dashboard'),
            route('filament.admin.resources.units.index') => __('filament.unit'),
            $this->getUrl() => __('filament.unit'),
        ];
    }
}
