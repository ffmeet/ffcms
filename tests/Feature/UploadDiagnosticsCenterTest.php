<?php

namespace Tests\Feature;

use App\Filament\Pages\UploadDiagnosticsCenter;
use App\Models\MemberGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class UploadDiagnosticsCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_upload_diagnostics_page(): void
    {
        File::ensureDirectoryExists(storage_path('logs'));
        File::put(
            storage_path('logs/upload-2026-04-09.log'),
            '[2026-04-09 04:24:28] local.WARNING: member.cover.failed.before_validation {"original_name":"demo.png","error_message":"封面上传失败：当前服务端上传上限仍然拦截了这张图片。","user_id":2,"upload_max_filesize":"2M","post_max_size":"8M"}'.PHP_EOL
        );

        $group = MemberGroup::create([
            'name' => '管理员组',
            'min_points' => 0,
            'max_points' => 999999,
        ]);

        $admin = User::create([
            'username' => 'upload-admin',
            'email' => 'upload-admin@example.com',
            'password_hash' => 'password',
            'group_id' => $group->id,
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->get(UploadDiagnosticsCenter::getUrl())
            ->assertOk()
            ->assertSee('上传记录中心')
            ->assertSee('入口与流程体检')
            ->assertSee('支付链路')
            ->assertSee('入口规则')
            ->assertSee('member.cover.failed.before_validation')
            ->assertSee('demo.png')
            ->assertSee('2M');
    }
}
