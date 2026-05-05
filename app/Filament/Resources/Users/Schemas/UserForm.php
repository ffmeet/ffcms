<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\MemberGroup;
use App\Models\User;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make([
                    'default' => 1,
                    'xl' => 12,
                ])->schema([
                    Section::make('会员概览')
                        ->compact()
                        ->schema([
                            Placeholder::make('member_overview')
                                ->hiddenLabel()
                                ->content(function (?User $record): HtmlString|string {
                                    if (! $record) {
                                        return '创建会员后可在这里看到头像、公开昵称和资料摘要。';
                                    }

                                    $avatar = $record->avatarUrl('medium') ?? $record->avatarUrl('small');
                                    $name = e($record->public_display_name);
                                    $email = e($record->email);
                                    $group = e($record->memberGroup?->name ?? '未分组');
                                    $createdAt = e(optional($record->created_at)->format('Y.m.d') ?: '未记录');
                                    $status = $record->status === 'active' ? '正常' : '停用';
                                    $avatarHtml = filled($avatar)
                                        ? '<img src="'.e($avatar).'" alt="'.$name.'" style="width:88px;height:88px;border-radius:9999px;object-fit:cover;border:1px solid #e2e8f0;" />'
                                        : '<span style="display:inline-flex;width:88px;height:88px;border-radius:9999px;align-items:center;justify-content:center;background:#f8fafc;color:#0f172a;font-size:32px;font-weight:700;border:1px solid #e2e8f0;">'.e(mb_substr($record->public_display_name, 0, 1)).'</span>';

                                    return new HtmlString(
                                        '<div style="display:flex;flex-direction:column;gap:20px;">'
                                        .'<div style="display:flex;align-items:center;gap:16px;">'
                                        .$avatarHtml
                                        .'<div style="display:flex;flex-direction:column;gap:4px;">'
                                        .'<div style="font-size:1.5rem;font-weight:700;color:#0f172a;line-height:1.1;">'.$name.'</div>'
                                        .'<div style="font-size:0.95rem;color:#64748b;">'.$email.'</div>'
                                        .'<div style="font-size:0.85rem;color:#94a3b8;">'.$record->backend_role_label.' · '.$status.'</div>'
                                        .'</div>'
                                        .'</div>'
                                        .'<div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px 16px;font-size:0.9rem;color:#475569;">'
                                        .'<div><div style="font-size:0.76rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;">会员组</div><div style="margin-top:4px;color:#0f172a;font-weight:600;">'.$group.'</div></div>'
                                        .'<div><div style="font-size:0.76rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;">加入时间</div><div style="margin-top:4px;color:#0f172a;font-weight:600;">'.$createdAt.'</div></div>'
                                        .'</div>'
                                        .'</div>'
                                    );
                                }),
                        ])
                        ->columnSpan([
                            'xl' => 5,
                        ]),

                    Group::make([
                        Section::make('账号与权限')
                            ->compact()
                            ->schema([
                                TextInput::make('username')
                                    ->label('用户名')
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
                                    ->preload()
                                    ->native(false),
                                Select::make('status')
                                    ->label('状态')
                                    ->required()
                                    ->options([
                                        'active' => '正常',
                                        'inactive' => '停用',
                                    ])
                                    ->default('active')
                                    ->native(false),
                                Toggle::make('is_staff')
                                    ->label('Staff 后台权限')
                                    ->inline(false),
                                Placeholder::make('account_type')
                                    ->label('账户类型')
                                    ->content(function (Get $get, ?User $record): string {
                                        $groupId = $get('group_id');
                                        $group = $groupId ? MemberGroup::query()->find($groupId) : $record?->memberGroup;

                                        if ($group?->hasPermission('admin.access')) {
                                            return '管理员';
                                        }

                                        return (bool) $get('is_staff') ? 'Staff' : '普通注册会员';
                                    }),
                                TextInput::make('password_hash')
                                    ->label('密码')
                                    ->password()
                                    ->revealable()
                                    ->afterStateHydrated(fn (TextInput $component): TextInput => $component->state(''))
                                    ->dehydrated(fn (?string $state): bool => filled($state))
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->minLength(6),
                            ]),
                    ])
                        ->columnSpan([
                            'xl' => 7,
                        ]),
                ]),
            ]);
    }
}
