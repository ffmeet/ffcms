<?php

namespace App\Filament\Resources\Posts\RelationManagers;

use App\Models\Comment;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    protected static ?string $title = '评论';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'username')
                    ->label('评论用户')
                    ->searchable()
                    ->preload(),
                Select::make('status')
                    ->required()
                    ->options([
                        'pending' => '待审核',
                        'approved' => '已通过',
                        'rejected' => '已驳回',
                    ])
                    ->default('pending')
                    ->native(false),
                Textarea::make('content')
                    ->required()
                    ->columnSpanFull()
                    ->rows(4),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('content')
            ->columns([
                TextColumn::make('user.username')
                    ->label('用户')
                    ->searchable(),
                TextColumn::make('content')
                    ->label('内容')
                    ->limit(60)
                    ->searchable(),
                BadgeColumn::make('status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => '待审核',
                        'approved' => '已通过',
                        'rejected' => '已驳回',
                        default => $state,
                    })
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
                TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => '待审核',
                        'approved' => '已通过',
                        'rejected' => '已驳回',
                    ]),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('通过')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Comment $record): bool => $record->status !== 'approved')
                    ->action(function (Comment $record): void {
                        $record->update(['status' => 'approved']);
                    }),
                Action::make('reject')
                    ->label('驳回')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Comment $record): bool => $record->status !== 'rejected')
                    ->action(function (Comment $record): void {
                        $record->update(['status' => 'rejected']);
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
