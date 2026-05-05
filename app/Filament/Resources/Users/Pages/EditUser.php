<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    public function getFormActionsAlignment(): string | Alignment
    {
        return Alignment::End;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['nickname_strategy'] = 'manual';

        if (($data['nickname'] ?? null) === ($data['username'] ?? null)) {
            $data['nickname_strategy'] = 'username';
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update(UserResource::normalizeProfileData($data));

        return $record->refresh();
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
