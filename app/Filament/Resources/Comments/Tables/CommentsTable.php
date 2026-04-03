<?php

namespace App\Filament\Resources\Comments\Tables;

use App\Models\Comment;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CommentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->extraAttributes(['class' => 'ecms-comments-table'])
            ->heading('评论')
            ->columns([
                ViewColumn::make('comment_card')
                    ->label('')
                    ->view('filament.tables.columns.comment-card'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('状态')
                    ->placeholder('所有状态')
                    ->options([
                        'pending' => '待审核',
                        'approved' => '已通过',
                        'rejected' => '已驳回',
                    ]),
            ], layout: FiltersLayout::AboveContent)
            ->deferFilters(false)
            ->filtersFormColumns(1)
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approveSelected')
                        ->label('批量通过')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records): void {
                            $records->each(fn (Comment $record) => $record->update(['status' => 'approved']));
                        }),
                    BulkAction::make('rejectSelected')
                        ->label('批量驳回')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records): void {
                            $records->each(fn (Comment $record) => $record->update(['status' => 'rejected']));
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
