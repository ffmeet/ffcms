<?php

use App\Models\MemberGroup;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('ffmeet:create-admin {username} {email} {--name=} {--password=}', function () {
    $username = trim((string) $this->argument('username'));
    $email = trim((string) $this->argument('email'));
    $displayName = trim((string) ($this->option('name') ?: $username));
    $password = (string) ($this->option('password') ?: '');

    if ($password === '') {
        $password = (string) $this->secret('请输入管理员密码（至少 6 位）');
    }

    if (mb_strlen($password) < 6) {
        $this->error('管理员密码至少 6 位。');

        return self::FAILURE;
    }

    $existing = User::query()
        ->where('username', $username)
        ->orWhere('email', $email)
        ->first();

    if ($existing) {
        $this->error('用户名或邮箱已存在，未创建管理员账号。');

        return self::FAILURE;
    }

    $adminGroup = MemberGroup::query()->firstOrCreate(
        ['name' => '管理员'],
        [
            'min_points' => 0,
            'max_points' => 999999,
            'permissions' => [
                'site.access' => true,
                'member.center' => true,
                'shop.access' => true,
                'events.access' => true,
                'events.priority' => true,
                'shop.discount' => true,
                'admin.access' => true,
            ],
        ],
    );

    $user = User::query()->create([
        'username' => $username,
        'display_name' => $displayName,
        'email' => $email,
        'password_hash' => $password,
        'group_id' => $adminGroup->id,
        'status' => 'active',
        'is_staff' => true,
        'headline' => '系统管理员',
        'bio' => '通过安装命令创建的首个后台管理员账号。',
    ]);

    $this->info('管理员账号创建成功。');
    $this->line('用户名：'.$user->username);
    $this->line('邮箱：'.$user->email);
    $this->line('后台入口：'.url('/admin/login'));

    return self::SUCCESS;
})->purpose('Create the first FFMeet admin account safely');
