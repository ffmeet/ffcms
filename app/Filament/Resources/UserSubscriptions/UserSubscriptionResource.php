<?php

namespace App\Filament\Resources\UserSubscriptions;

use App\Filament\Resources\UserSubscriptions\Pages\EditUserSubscription;
use App\Filament\Resources\UserSubscriptions\Pages\ListUserSubscriptions;
use App\Filament\Resources\UserSubscriptions\Schemas\UserSubscriptionForm;
use App\Filament\Resources\UserSubscriptions\Tables\UserSubscriptionsTable;
use App\Models\UserSubscription;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserSubscriptionResource extends Resource
{
    protected static ?string $model = UserSubscription::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static ?string $navigationLabel = '用户订阅';

    protected static ?string $modelLabel = '用户订阅';

    protected static ?string $pluralModelLabel = '用户订阅';

    protected static string|\UnitEnum|null $navigationGroup = '商业化';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return UserSubscriptionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserSubscriptionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUserSubscriptions::route('/'),
            'edit' => EditUserSubscription::route('/{record}/edit'),
        ];
    }
}
