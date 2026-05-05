<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\MemberResetPasswordNotification;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Tilto\Commentable\Contracts\Commenter;
use Tilto\Commentable\Traits\IsCommenter;

class User extends Authenticatable implements CanResetPasswordContract, Commenter, FilamentUser, HasName
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use CanResetPassword;
    use HasFactory;
    use IsCommenter;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'display_name',
        'first_name',
        'last_name',
        'nickname',
        'email',
        'password_hash',
        'group_id',
        'status',
        'is_staff',
        'avatar_original_path',
        'avatar_large_path',
        'avatar_medium_path',
        'avatar_small_path',
        'headline',
        'bio',
        'social_links',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password_hash' => 'hashed',
            'is_staff' => 'boolean',
            'social_links' => 'array',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->status === 'active' && $this->is_staff_account;
    }

    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    public function getFilamentName(): string
    {
        return $this->public_display_name;
    }

    public function getCommenterName(): string
    {
        return $this->public_display_name;
    }

    public function getPublicDisplayNameAttribute(): string
    {
        if (filled($this->nickname)) {
            return trim((string) $this->nickname);
        }

        if (filled($this->display_name)) {
            return trim((string) $this->display_name);
        }

        $generated = trim(implode(' ', array_filter([
            $this->first_name,
            $this->last_name,
        ])));

        return $generated !== '' ? $generated : '会员用户';
    }

    public function getIsAdminAccountAttribute(): bool
    {
        return $this->memberGroup?->hasPermission('admin.access') ?? false;
    }

    public function getIsStaffAccountAttribute(): bool
    {
        return $this->is_staff || $this->is_admin_account;
    }

    public function getBackendRoleLabelAttribute(): string
    {
        if ($this->is_admin_account) {
            return '管理员';
        }

        if ($this->is_staff_account) {
            return 'Staff';
        }

        return '普通会员';
    }

    public static function resolveNickname(string $strategy, ?string $firstName, ?string $lastName, ?string $manualNickname, ?string $username = null): ?string
    {
        $firstName = trim((string) $firstName);
        $lastName = trim((string) $lastName);
        $manualNickname = trim((string) $manualNickname);
        $username = trim((string) $username);

        return match ($strategy) {
            'username' => $username,
            'last_first' => trim(implode(' ', array_filter([$lastName, $firstName]))),
            'first_last' => trim(implode(' ', array_filter([$firstName, $lastName]))),
            default => $manualNickname,
        };
    }

    public function getAuthorHeadlineAttribute(): string
    {
        if (filled($this->headline)) {
            return (string) $this->headline;
        }

        return $this->memberGroup?->name
            ? $this->memberGroup->name.'作者'
            : '特约作者';
    }

    public function getAuthorBioAttribute(): string
    {
        if (filled($this->bio)) {
            return (string) $this->bio;
        }

        return '关注内容、专题与当代生活方式叙事，持续分享公开文章与栏目观察。';
    }

    public function memberGroup(): BelongsTo
    {
        return $this->belongsTo(MemberGroup::class, 'group_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new MemberResetPasswordNotification($token));
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function eventRegistrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function hasMemberPermission(string $permission): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($permission === 'member.center' && ($this->is_staff || $this->memberGroup?->hasPermission('admin.access'))) {
            return true;
        }

        return $this->memberGroup?->hasPermission($permission) ?? false;
    }

    public function hasActiveSubscription(): bool
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where(function ($query): void {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->exists();
    }

    public function canAccessMemberGroup(?MemberGroup $requiredGroup): bool
    {
        if (! $requiredGroup) {
            return true;
        }

        $currentGroup = $this->memberGroup;

        if (! $currentGroup) {
            return false;
        }

        return (int) $currentGroup->min_points >= (int) $requiredGroup->min_points;
    }

    public function avatarUrl(string $size = 'small'): ?string
    {
        $path = match ($size) {
            'large' => $this->avatar_large_path,
            'medium' => $this->avatar_medium_path,
            'original' => $this->avatar_original_path,
            default => $this->avatar_small_path,
        };

        if (! filled($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatarUrl();
    }
}
