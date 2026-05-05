<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MemberGroup extends Model
{
    use HasFactory;

    public const CORE_PERMISSIONS = [
        'site.access' => '前台站点访问',
        'member.center' => '会员中心访问',
        'shop.access' => '商店浏览与购买',
        'events.access' => '活动浏览与报名',
        'events.priority' => '活动优先资格',
        'shop.discount' => '商店折扣权益',
        'admin.access' => '后台管理访问',
    ];

    protected $fillable = [
        'name',
        'min_points',
        'max_points',
        'permissions',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'group_id');
    }

    public static function corePermissionOptions(): array
    {
        return self::CORE_PERMISSIONS;
    }

    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissions ?? [];

        if (array_key_exists($permission, $permissions)) {
            return filter_var($permissions[$permission], FILTER_VALIDATE_BOOL)
                || $permissions[$permission] === true
                || $permissions[$permission] === 1
                || $permissions[$permission] === '1';
        }

        return $this->legacyPermissionFallback($permission);
    }

    public function enabledPermissions(): array
    {
        return collect($this->permissions ?? [])
            ->filter(fn ($value): bool => filter_var($value, FILTER_VALIDATE_BOOL) || $value === true || $value === 1 || $value === '1')
            ->keys()
            ->all();
    }

    public function enabledPermissionLabels(): array
    {
        return collect($this->enabledPermissions())
            ->map(fn (string $permission): string => self::CORE_PERMISSIONS[$permission] ?? $permission)
            ->values()
            ->all();
    }

    protected function legacyPermissionFallback(string $permission): bool
    {
        if (in_array($permission, ['site.access', 'member.center'], true)) {
            return true;
        }

        if ($permission === 'admin.access') {
            return str_contains(mb_strtolower($this->name), 'admin')
                || str_contains($this->name, '管理');
        }

        return false;
    }
}
