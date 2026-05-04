<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    public function getFormActionsAlignment(): string | Alignment
    {
        return Alignment::End;
    }

    protected function handleRecordCreation(array $data): Model
    {
        return User::create(UserResource::normalizeProfileData($data))->refresh();
    }
}
