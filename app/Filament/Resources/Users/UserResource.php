<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = '会员';

    protected static ?string $modelLabel = '会员';

    protected static ?string $pluralModelLabel = '会员';

    protected static string|\UnitEnum|null $navigationGroup = '工作流';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    public static function normalizeProfileData(array $data): array
    {
        $data['first_name'] = blank($data['first_name'] ?? null) ? null : trim((string) $data['first_name']);
        $data['last_name'] = blank($data['last_name'] ?? null) ? null : trim((string) $data['last_name']);
        $data['bio'] = blank($data['bio'] ?? null) ? null : trim((string) $data['bio']);

        $strategy = (string) ($data['nickname_strategy'] ?? 'manual');
        $manualNickname = blank($data['nickname'] ?? null) ? null : trim((string) $data['nickname']);
        $username = trim((string) ($data['username'] ?? ''));

        $data['nickname'] = User::resolveNickname(
            $strategy,
            $data['first_name'] ?? null,
            $data['last_name'] ?? null,
            $manualNickname,
            $username,
        );

        if ($data['nickname'] === '') {
            $data['nickname'] = null;
        }

        unset($data['nickname_strategy']);

        return $data;
    }
}
