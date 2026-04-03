<?php

namespace App\Filament\Resources\Attachments\Pages;

use App\Filament\Resources\Attachments\AttachmentResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Filament\Resources\Pages\CreateRecord;

class CreateAttachment extends CreateRecord
{
    protected static string $resource = AttachmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
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

    protected function handleRecordCreation(array $data): Model
    {
        if (blank($data['user_id'] ?? null) && auth()->id()) {
            $data['user_id'] = auth()->id();
        }

        /** @var Model $record */
        $record = static::getModel()::create($data);

        return $record;
    }
}
