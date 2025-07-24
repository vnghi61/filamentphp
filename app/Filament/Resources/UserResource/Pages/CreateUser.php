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

}
