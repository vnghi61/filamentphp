<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
use App\Traits\RedirectTraits;

class CreateUser extends CreateRecord
{
    use RedirectTraits;
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['password'])) {
            $randomPassword = Str::random(8);
            $data['password'] = bcrypt($randomPassword);
        }

        return $data;
    }

    public static function getnavigationLabel(): string
    {
        return __('filament.user_create');
    }

    public function getBreadcrumbs(): array
    {
        return [
            route('filament.admin.pages.dashboard') => __('filament.dashboard'),
            route('filament.admin.resources.users.index') => __('filament.user'),
            $this->getUrl() => __('filament.user_create'),
        ];
    }
}
