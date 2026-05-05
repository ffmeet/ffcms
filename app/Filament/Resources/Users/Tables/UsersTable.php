<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('username')
                    ->label('用户名')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('邮箱')
                    ->searchable(),
                TextColumn::make('memberGroup.name')
                    ->label('会员组')
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('backend_role_label')
                    ->label('账号类型')
                    ->colors([
                        'danger' => '管理员',
                        'warning' => 'Staff',
                        'gray' => '普通会员',
                    ]),
                TextColumn::make('posts_count')
                    ->label('文章数')
                    ->counts('posts')
                    ->sortable(),
                TextColumn::make('comments_count')
                    ->label('评论数')
                    ->counts('comments')
                    ->sortable(),
                BadgeColumn::make('status')
                    ->formatStateUsing(fn (string $state): string => $state === 'active' ? '正常' : '停用')
                    ->colors([
                        'success' => 'active',
                        'gray' => 'inactive',
                    ]),
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
                SelectFilter::make('group_id')
                    ->relationship('memberGroup', 'name')
                    ->label('会员组'),
                SelectFilter::make('status')
                    ->options([
                        'active' => '正常',
                        'inactive' => '停用',
                    ]),
                SelectFilter::make('is_staff')
                    ->label('后台权限')
                    ->options([
                        '1' => 'Staff / 管理员',
                        '0' => '仅普通会员',
                    ]),
            ])
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
