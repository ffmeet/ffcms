<?php

namespace App\Filament\Resources\Attachments\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AttachmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'username')
                    ->label('上传用户')
                    ->searchable()
                    ->preload()
                    ->required(),
                FileUpload::make('upload')
                    ->label('上传文件')
                    ->disk('public')
                    ->directory('attachments')
                    ->visibility('public')
                    ->maxSize(10240)
                    ->preserveFilenames()
                    ->dehydrated(false)
                    ->helperText('当前阶段先使用本地 public 存储，单个文件建议控制在 10MB 以内，后续再扩展云存储和更复杂的上传流程。')
                    ->columnSpanFull(),
                TextInput::make('filename')
                    ->label('文件名')
                    ->required()
                    ->maxLength(255),
                TextInput::make('filepath')
                    ->label('存储路径')
                    ->required()
                    ->maxLength(255),
                TextInput::make('mime_type')
                    ->label('MIME 类型')
                    ->maxLength(255),
                TextInput::make('size')
                    ->label('文件大小（字节）')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                Placeholder::make('preview_url')
                    ->label('访问地址')
                    ->content(fn ($record): string => $record?->url ?? '保存后显示'),
            ]);
    }
}
