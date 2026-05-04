<?php

namespace App\Filament\Resources\MemberGroups\Schemas;

use App\Models\MemberGroup;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MemberGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('会员组信息')
                    ->schema([
                        TextInput::make('name')
                            ->label('会员组名称')
                            ->required(),
                        TextInput::make('min_points')
                            ->label('最低成长值')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        TextInput::make('max_points')
                            ->label('最高成长值')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ])
                    ->columns(3),
                Section::make('核心权限')
                    ->schema([
                        ToggleButtons::make('core_permissions')
                            ->label('快速配置')
                            ->options(MemberGroup::corePermissionOptions())
                            ->multiple()
                            ->inline()
                            ->dehydrated(false)
                            ->afterStateHydrated(function (ToggleButtons $component, $state, ?MemberGroup $record): void {
                                $component->state($record?->enabledPermissions() ?? []);
                            })
                            ->afterStateUpdated(function ($state, callable $set, ?MemberGroup $record): void {
                                $existing = collect($record?->permissions ?? [])
                                    ->map(fn ($value): bool => filter_var($value, FILTER_VALIDATE_BOOL) || $value === true || $value === 1 || $value === '1')
                                    ->all();

                                foreach (array_keys(MemberGroup::corePermissionOptions()) as $permission) {
                                    unset($existing[$permission]);
                                }

                                foreach (($state ?? []) as $permission) {
                                    $existing[$permission] = true;
                                }

                                $set('permissions', $existing);
                            })
                            ->helperText('先用这里配置主要访问能力，细粒度权限再放到下面的扩展权限里。'),
                    ]),
                Section::make('扩展权限')
                    ->schema([
                        KeyValue::make('permissions')
                            ->label('扩展权限')
                            ->columnSpanFull()
                            ->keyLabel('权限标识')
                            ->valueLabel('权限值')
                            ->helperText('核心权限已在上方快速配置，这里保留给额外权限，例如 post.publish => true。'),
                    ]),
            ]);
    }
}
