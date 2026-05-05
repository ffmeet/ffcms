<?php

namespace App\Filament\Resources\MemberGroups\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MemberGroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->heading('会员组台')
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('会员组')
                    ->searchable(),
                TextColumn::make('min_points')
                    ->label('最低成长值')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_points')
                    ->label('最高成长值')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('permissions_summary')
                    ->label('核心权限')
                    ->state(fn ($record): string => collect($record->enabledPermissionLabels())->take(3)->implode(' · '))
                    ->placeholder('未配置')
                    ->wrap(),
                TextColumn::make('users_count')
                    ->label('会员数')
                    ->counts('users')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('usage')
                    ->label('使用情况')
                    ->placeholder('全部会员组')
                    ->options([
                        'used' => '已有会员使用',
                        'unused' => '尚未分配会员',
                    ])
                    ->query(function ($query, array $data) {
                        return match ($data['value'] ?? null) {
                            'used' => $query->has('users'),
                            'unused' => $query->doesntHave('users'),
                            default => $query,
                        };
                    }),
                SelectFilter::make('permission_setup')
                    ->label('权限配置')
                    ->placeholder('全部状态')
                    ->options([
                        'configured' => '已配置权限',
                        'empty' => '未配置权限',
                    ])
                    ->query(function ($query, array $data) {
                        return match ($data['value'] ?? null) {
                            'configured' => $query->where('permissions', '!=', '[]')->whereNotNull('permissions'),
                            'empty' => $query->where(function ($subQuery) {
                                $subQuery->whereNull('permissions')
                                    ->orWhere('permissions', '[]');
                            }),
                            default => $query,
                        };
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->deferFilters(false)
            ->filtersFormColumns(2)
            ->emptyStateHeading('还没有会员组')
            ->emptyStateDescription('会员组会承接前台、会员中心、活动和后台访问权限，建议先配置默认组和管理员组。')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
