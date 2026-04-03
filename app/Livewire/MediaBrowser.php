<?php

namespace App\Livewire;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\File as FileFacade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Slimani\MediaManager\Models\File;
use Slimani\MediaManager\Models\Tag;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaBrowser extends \Slimani\MediaManager\Livewire\MediaBrowser
{
    public function mount(
        bool $isPicker = false,
        bool $multiple = false,
        ?string $pickerId = null,
        array $selectedItems = [],
        ?string $onSelect = null,
        ?string $statePath = null,
        ?array $acceptedFileTypes = [],
        ?int $currentFolderId = null
    ): void {
        parent::mount(
            isPicker: $isPicker,
            multiple: $multiple,
            pickerId: $pickerId,
            selectedItems: $selectedItems,
            onSelect: $onSelect,
            statePath: $statePath,
            acceptedFileTypes: $acceptedFileTypes,
            currentFolderId: $currentFolderId,
        );

        if (blank($this->sortField)) {
            $this->sortField = 'created_at';
        }

        if (blank($this->sortDirection) || $this->sortDirection === 'asc') {
            $this->sortDirection = 'desc';
        }
    }

    protected function fileDetailsSchema(File $file): array
    {
        $schema = parent::fileDetailsSchema($file);
        $webpToolInserted = false;

        foreach ($schema as $index => $component) {
            if (! $component instanceof TextEntry) {
                continue;
            }

            if ($component->getName() === 'sel_name') {
                $schema[$index] = $component->weight(FontWeight::Bold);
            }

            if ($component->getName() === 'sel_path') {
                $schema[$index] = $component;

                if ($this->canConvertToWebp($file)) {
                    array_splice($schema, $index + 1, 0, [
                        TextEntry::make('sel_image_tools')
                            ->label('图片处理')
                            ->state('可直接将当前图片转换为 WebP，以减小体积。')
                            ->suffixAction($this->convertToWebpAction($file)),
                    ]);
                    $webpToolInserted = true;
                }
            }
        }

        if (! $webpToolInserted && $this->canConvertToWebp($file)) {
            $schema[] = TextEntry::make('sel_image_tools')
                ->label('图片处理')
                ->state('可直接将当前图片转换为 WebP，以减小体积。')
                ->suffixAction($this->convertToWebpAction($file));
        }

        return $schema;
    }

    public function uploadAction(): Action
    {
        return Action::make('upload')
            ->label('Upload')
            ->icon('heroicon-m-arrow-up-tray')
            ->schema([
                FileUpload::make('files')
                    ->label('Files')
                    ->multiple()
                    ->preserveFilenames()
                    ->disk(config('livewire.temporary_file_upload.disk'))
                    ->directory(config('livewire.temporary_file_upload.directory'))
                    ->required(),
                TagsInput::make('tags')
                    ->suggestions(Tag::pluck('name')->toArray()),
                TextInput::make('caption'),
                TextInput::make('alt_text'),
            ])
            ->action(fn (array $data): mixed => $this->handleUploadedFiles($data));
    }

    public function handleUploadedFiles(array $data): array
    {
        $createdIds = [];

        foreach ($data['files'] as $file) {
            $filename = $file instanceof UploadedFile
                ? $file->getClientOriginalName()
                : basename((string) $file);

            $name = pathinfo($filename, PATHINFO_FILENAME);

            $fileModel = File::create([
                'name' => $name,
                'uploaded_by_user_id' => auth()->id(),
                'folder_id' => $this->currentFolderId,
                'caption' => $data['caption'] ?? null,
                'alt_text' => $data['alt_text'] ?? null,
            ]);

            if (isset($data['tags'])) {
                $tagIds = collect($data['tags'])
                    ->map(fn (string $tagName): int => Tag::firstOrCreate(['name' => $tagName])->id)
                    ->toArray();

                $fileModel->tags()->sync($tagIds);
            }

            try {
                $media = $this->attachUploadedMedia($fileModel, $file, $filename);

                $fileModel->update([
                    'size' => $media->size,
                    'mime_type' => $media->mime_type,
                    'extension' => $media->extension,
                    'width' => $media->getCustomProperty('width'),
                    'height' => $media->getCustomProperty('height'),
                ]);

                $createdIds[] = $fileModel->id;
            } catch (\Throwable $exception) {
                Log::error('Media Manager Upload Error: '.$exception->getMessage(), [
                    'file' => $filename,
                    'disk' => config('livewire.temporary_file_upload.disk'),
                    'directory' => config('livewire.temporary_file_upload.directory'),
                ]);

                $fileModel->delete();

                Notification::make()
                    ->danger()
                    ->title("上传失败：{$filename}")
                    ->body($exception->getMessage())
                    ->send();
            }
        }

        if ($createdIds !== []) {
            $latestId = end($createdIds);

            $this->sortField = 'created_at';
            $this->sortDirection = 'desc';
            $this->selectedItems = ["file-{$latestId}"];
            $this->selectedFileId = $latestId;
            $this->showSelectedOnly = false;
            $this->locateItem("file-{$latestId}");
            $this->dispatch('media-uploaded');
            $this->clearCachedSchemas();

            Notification::make()
                ->success()
                ->title('上传完成')
                ->body('新文件已自动定位到列表顶部。')
                ->send();
        }

        return $createdIds;
    }

    protected function convertToWebpAction(File $file): Action
    {
        return Action::make('convertToWebp')
            ->label('转为 WebP')
            ->icon('heroicon-o-arrow-path-rounded-square')
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading('转为 WebP')
            ->modalDescription('会将当前图片替换为 WebP 格式，并重新生成缩略图。')
            ->visible(fn (): bool => $this->canConvertToWebp($file))
            ->action(fn (): mixed => $this->convertFileToWebp($file));
    }

    protected function canConvertToWebp(File $file): bool
    {
        return str($file->mime_type)->startsWith('image/') && $file->extension !== 'webp';
    }

    protected function convertFileToWebp(File $file): void
    {
        if (! $this->canConvertToWebp($file)) {
            Notification::make()
                ->warning()
                ->title('当前文件不支持转换为 WebP')
                ->send();

            return;
        }

        $media = $file->getFirstMedia('default') ?? $file->getFirstMedia();

        if (! $media instanceof Media) {
            Notification::make()
                ->danger()
                ->title('未找到原始媒体文件')
                ->send();

            return;
        }

        $sourcePath = $media->getPath();

        if (! is_string($sourcePath) || ! FileFacade::exists($sourcePath)) {
            Notification::make()
                ->danger()
                ->title('原始文件不存在，无法转换')
                ->send();

            return;
        }

        $image = $this->createImageResource($sourcePath);

        if ($image === false) {
            Notification::make()
                ->danger()
                ->title('当前图片格式暂不支持转换')
                ->send();

            return;
        }

        $baseName = Str::slug(pathinfo($media->file_name, PATHINFO_FILENAME));
        $targetPath = storage_path('app/tmp/'.$baseName.'-'.Str::uuid().'.webp');
        FileFacade::ensureDirectoryExists(dirname($targetPath));

        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        $converted = imagewebp($image, $targetPath, 82);
        imagedestroy($image);

        if (! $converted || ! FileFacade::exists($targetPath)) {
            Notification::make()
                ->danger()
                ->title('WebP 转换失败')
                ->send();

            return;
        }

        try {
            [$width, $height] = array_pad((array) getimagesize($targetPath), 2, null);
            $size = FileFacade::size($targetPath);
            $webpName = Str::finish($file->name, ' ').'WebP';

            $webpFile = File::create([
                'folder_id' => $file->folder_id,
                'uploaded_by_user_id' => $file->uploaded_by_user_id ?? auth()->id(),
                'name' => $webpName,
                'caption' => $file->caption,
                'alt_text' => $file->alt_text,
            ]);

            $webpFile->addMedia($targetPath)
                ->usingName($webpName)
                ->usingFileName($baseName.'.webp')
                ->toMediaCollection('default');

            $webpFile->forceFill([
                'extension' => 'webp',
                'mime_type' => 'image/webp',
                'size' => $size,
                'width' => $width,
                'height' => $height,
            ])->save();

            $this->selectedItems = ["file-{$webpFile->id}"];
            $this->selectedFileId = $webpFile->id;
            $this->locateItem("file-{$webpFile->id}");
        } finally {
            FileFacade::delete($targetPath);
        }

        $this->clearCachedSchemas();

        Notification::make()
            ->success()
            ->title('已生成 WebP 文件')
            ->send();
    }

    protected function createImageResource(string $sourcePath): mixed
    {
        $contents = FileFacade::get($sourcePath);

        if ($contents === '') {
            return false;
        }

        return @imagecreatefromstring($contents);
    }

    protected function attachUploadedMedia(File $fileModel, UploadedFile|string $file, string $filename): Media
    {
        $diskName = $this->getMediaDiskName();

        if ($file instanceof UploadedFile) {
            return $fileModel->addMediaFromString($file->get())
                ->usingFileName($filename)
                ->toMediaCollection('default', $diskName);
        }

        $temporaryDiskName = config('livewire.temporary_file_upload.disk') ?: config('filesystems.default');
        $temporaryDirectory = trim((string) config('livewire.temporary_file_upload.directory', 'livewire-tmp'), '/');
        $temporaryDisk = Storage::disk($temporaryDiskName);

        $pathsToTry = array_values(array_unique([
            (string) $file,
            $temporaryDirectory.'/'.ltrim((string) $file, '/'),
        ]));

        $actualPath = null;

        foreach ($pathsToTry as $candidate) {
            if ($temporaryDisk->exists($candidate)) {
                $actualPath = $candidate;
                break;
            }
        }

        if (! $actualPath) {
            throw new \RuntimeException('临时上传文件不存在，无法写入媒体库。');
        }

        $content = $temporaryDisk->get($actualPath);

        $media = $fileModel->addMediaFromString($content)
            ->usingFileName($filename)
            ->toMediaCollection('default', $diskName);

        $temporaryDisk->delete($actualPath);

        return $media;
    }

    protected function getMediaDiskName(): string
    {
        try {
            return filament('media-manager')->getDisk();
        } catch (\Throwable) {
            return config('media-library.disk_name', 'public');
        }
    }
}
