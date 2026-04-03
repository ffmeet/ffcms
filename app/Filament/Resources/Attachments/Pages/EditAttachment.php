<?php

namespace App\Filament\Resources\Attachments\Pages;

use App\Filament\Resources\Attachments\AttachmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EditAttachment extends EditRecord
{
    protected static string $resource = AttachmentResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['upload'] = null;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $uploadedPath = $data['upload'] ?? null;

        unset($data['upload']);

        if (blank($uploadedPath)) {
            return $data;
        }

        $data['filepath'] = $uploadedPath;
        $data['filename'] = basename($uploadedPath);
        $data['mime_type'] = Storage::disk('public')->mimeType($uploadedPath) ?: $data['mime_type'] ?? null;
        $data['size'] = Storage::disk('public')->size($uploadedPath) ?: (int) ($data['size'] ?? 0);

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update($data);

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
