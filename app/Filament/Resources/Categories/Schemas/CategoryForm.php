<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('model_id')
                    ->relationship('contentModel', 'name')
                    ->label('绑定模型')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (mixed $state, Set $set): void {
                        if (blank($state)) {
                            $set('parent_id', null);
                            $set('level', 0);
                        }
                    }),
                Select::make('parent_id')
                    ->label('父栏目')
                    ->options(fn (Get $get): array => Category::query()
                        ->when(filled($get('model_id')), fn ($query) => $query->where('model_id', $get('model_id')))
                        ->orderBy('sort_order')
                        ->orderBy('id')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (mixed $state, Set $set): void {
                        if (blank($state)) {
                            $set('level', 0);

                            return;
                        }

                        $parent = Category::query()->find($state);

                        if (! $parent) {
                            $set('level', 0);

                            return;
                        }

                        $set('model_id', $parent->model_id);
                        $set('level', (int) $parent->level + 1);
                    }),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->helperText('用于 URL，例如 news 或 company-updates'),
                Textarea::make('description')
                    ->columnSpanFull()
                    ->rows(4),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                TextInput::make('level')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->readOnly()
                    ->helperText('系统会根据父栏目自动计算层级'),
            ]);
    }
}
