<?php

namespace Tests\Feature;

use App\Livewire\MediaBrowser;
use App\Models\MemberGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class MediaBrowserTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_browser_defaults_to_latest_first_sorting(): void
    {
        Livewire::test(MediaBrowser::class)
            ->assertSet('sortField', 'created_at')
            ->assertSet('sortDirection', 'desc');
    }

    public function test_media_browser_can_persist_uploaded_file_from_livewire_temp_storage(): void
    {
        Storage::fake('public');

        $group = MemberGroup::create([
            'name' => '媒体测试组',
            'min_points' => 0,
            'max_points' => 999999,
        ]);

        $user = User::create([
            'username' => 'media-admin',
            'email' => 'media-admin@example.com',
            'password_hash' => 'password',
            'group_id' => $group->id,
            'status' => 'active',
        ]);

        $temporaryPath = 'livewire-tmp/test-upload.png';
        $imageBinary = File::get(base_path('storage/app/public/3/avatar-admin.png'));

        Storage::disk('public')->put($temporaryPath, $imageBinary);

        $this->actingAs($user);

        Livewire::test(MediaBrowser::class)
            ->call('handleUploadedFiles', [
                'files' => ['test-upload.png'],
                'caption' => '测试上传',
                'alt_text' => '测试图片',
                'tags' => ['测试'],
            ])
            ->assertSet('sortField', 'created_at')
            ->assertSet('sortDirection', 'desc');

        $this->assertDatabaseHas('media_files', [
            'name' => 'test-upload',
            'mime_type' => 'image/png',
            'extension' => 'png',
        ]);

        Storage::disk('public')->assertMissing($temporaryPath);
    }
}
