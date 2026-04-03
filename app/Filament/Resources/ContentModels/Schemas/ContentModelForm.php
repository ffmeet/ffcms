<?php

namespace App\Filament\Resources\ContentModels\Schemas;

use App\Support\ContentModelFieldManager;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ContentModelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('table_name')
                    ->required()
                    ->maxLength(255)
                    ->helperText('逻辑表名，用于区分不同内容模型，例如 posts_news'),
                Repeater::make('field_config')
                    ->label('模型字段')
                    ->columnSpanFull()
                    ->default([])
                    ->reorderable(false)
                    ->addActionLabel('新增字段')
                    ->schema([
                        TextInput::make('name')
                            ->label('字段名')
                            ->required()
                            ->distinct()
                            ->live(onBlur: true)
                            ->regex('/^[a-z][a-z0-9_]*$/')
                            ->validationMessages([
                                'regex' => '字段名只能使用小写字母、数字和下划线，且必须以字母开头。',
                            ])
                            ->rule(function (): \Closure {
                                return function (string $attribute, mixed $value, \Closure $fail): void {
                                    if (in_array((string) $value, ContentModelFieldManager::RESERVED_NAMES, true)) {
                                        $fail('该字段名是系统保留字段，请换一个名称。');
                                    }
                                };
                            }),
                        TextInput::make('label')
                            ->label('显示名')
                            ->required(),
                        Select::make('type')
                            ->label('字段类型')
                            ->options(array_combine(
                                ContentModelFieldManager::SUPPORTED_TYPES,
                                array_map(
                                    static fn (string $type): string => match ($type) {
                                        'text' => '单行文本',
                                        'textarea' => '多行文本',
                                        'number' => '数字',
                                        'select' => '下拉选择',
                                        'toggle' => '开关',
                                        default => $type,
                                    },
                                    ContentModelFieldManager::SUPPORTED_TYPES,
                                ),
                            ))
                            ->required()
                            ->native(false)
                            ->live(),
                        Toggle::make('required')
                            ->label('必填')
                            ->default(false),
                        TagsInput::make('options')
                            ->label('可选项')
                            ->placeholder('输入选项后按回车')
                            ->splitKeys(['Tab', ','])
                            ->hidden(fn (Get $get): bool => $get('type') !== 'select')
                            ->dehydrated(fn (Get $get): bool => $get('type') === 'select')
                            ->required(fn (Get $get): bool => $get('type') === 'select')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->helperText('V1 支持 text、textarea、number、select、toggle；select 可继续配置候选项。'),
            ]);
    }
}
