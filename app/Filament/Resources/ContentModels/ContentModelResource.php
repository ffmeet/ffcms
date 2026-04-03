<?php

namespace App\Filament\Resources\ContentModels;

use App\Filament\Resources\ContentModels\Pages\CreateContentModel;
use App\Filament\Resources\ContentModels\Pages\EditContentModel;
use App\Filament\Resources\ContentModels\Pages\ListContentModels;
use App\Filament\Resources\ContentModels\Schemas\ContentModelForm;
use App\Filament\Resources\ContentModels\Tables\ContentModelsTable;
use App\Models\ContentModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ContentModelResource extends Resource
{
    protected static ?string $model = ContentModel::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCircleStack;

    protected static ?string $navigationLabel = '内容模型';

    protected static ?string $modelLabel = '内容模型';

    protected static ?string $pluralModelLabel = '内容模型';

    protected static string|\UnitEnum|null $navigationGroup = '网站设置';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return ContentModelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContentModelsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContentModels::route('/'),
            'create' => CreateContentModel::route('/create'),
            'edit' => EditContentModel::route('/{record}/edit'),
        ];
    }
}
