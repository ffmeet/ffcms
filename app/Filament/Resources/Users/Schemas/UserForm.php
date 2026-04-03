<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('username')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('邮箱')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Select::make('group_id')
                    ->relationship('memberGroup', 'name')
                    ->label('会员组')
                    ->searchable()
                    ->preload(),
                Select::make('status')
                    ->required()
                    ->options([
                        'active' => '正常',
                        'inactive' => '停用',
                    ])
                    ->default('active')
                    ->native(false),
                TextInput::make('password_hash')
                    ->label('密码')
                    ->password()
                    ->revealable()
                    ->afterStateHydrated(fn (TextInput $component): TextInput => $component->state(''))
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->minLength(6),
            ]);
    }
}
