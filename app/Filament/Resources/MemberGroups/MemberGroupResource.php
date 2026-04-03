<?php

namespace App\Filament\Resources\MemberGroups;

use App\Filament\Resources\MemberGroups\Pages\CreateMemberGroup;
use App\Filament\Resources\MemberGroups\Pages\EditMemberGroup;
use App\Filament\Resources\MemberGroups\Pages\ListMemberGroups;
use App\Filament\Resources\MemberGroups\Schemas\MemberGroupForm;
use App\Filament\Resources\MemberGroups\Tables\MemberGroupsTable;
use App\Models\MemberGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MemberGroupResource extends Resource
{
    protected static ?string $model = MemberGroup::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = '会员组';

    protected static ?string $modelLabel = '会员组';

    protected static ?string $pluralModelLabel = '会员组';

    protected static string|\UnitEnum|null $navigationGroup = '网站设置';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return MemberGroupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MemberGroupsTable::configure($table);
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
            'index' => ListMemberGroups::route('/'),
            'create' => CreateMemberGroup::route('/create'),
            'edit' => EditMemberGroup::route('/{record}/edit'),
        ];
    }
}
