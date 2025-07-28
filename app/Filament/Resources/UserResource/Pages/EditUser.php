<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Traits\RedirectTraits;

class EditUser extends EditRecord
{
    use RedirectTraits;
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return __('filament.user_edit');
    }

    public function getBreadcrumbs(): array
    {
        return [
            route('filament.admin.pages.dashboard') => __('filament.dashboard'),
            route('filament.admin.resources.users.index') => __('filament.user'),
        ];
    }
}
