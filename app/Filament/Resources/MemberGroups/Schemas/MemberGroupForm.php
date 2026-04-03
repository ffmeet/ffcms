<?php

namespace App\Filament\Resources\MemberGroups\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MemberGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('min_points')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                TextInput::make('max_points')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                KeyValue::make('permissions')
                    ->columnSpanFull()
                    ->keyLabel('权限标识')
                    ->valueLabel('权限值')
                    ->helperText('例如：admin.access => true，post.publish => true'),
            ]);
    }
}
