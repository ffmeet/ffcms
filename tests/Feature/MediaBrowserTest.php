<?php

namespace Tests\Feature;

use App\Livewire\MediaBrowser;
use App\Models\Attachment;
use App\Models\Category;
use App\Models\ContentModel;
use App\Models\MemberGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Slimani\MediaManager\Models\File as MediaFile;
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
        Storage::persistentFake('public');
        Storage::persistentFake('local');

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

        Storage::disk('local')->put($temporaryPath, $imageBinary);

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

        Storage::disk('local')->assertMissing($temporaryPath);
    }

    public function test_media_urls_use_relative_storage_paths_by_default(): void
    {
        config()->set('app.url', 'http://localhost:8000');
        config()->set('filesystems.disks.public.url', '/storage');

        $file = MediaFile::create([
            'name' => 'relative-url-test',
        ]);

        $file->addMedia(base_path('storage/app/public/3/avatar-admin.png'))
            ->preservingOriginal()
            ->toMediaCollection('default', 'public');

        $file->refresh();

        $this->assertStringStartsWith('/storage/', $file->getUrl('preview'));
    }

    public function test_media_browser_can_find_cover_uploaded_from_member_submission_flow(): void
    {
        Storage::persistentFake('public');

        $group = MemberGroup::create([
            'name' => '会员投稿媒体测试组',
            'min_points' => 0,
            'max_points' => 999999,
        ]);

        $user = User::create([
            'username' => 'member-media-owner',
            'email' => 'member-media-owner@example.com',
            'password_hash' => 'password',
            'group_id' => $group->id,
            'status' => 'active',
        ]);

        $model = ContentModel::create([
            'name' => '新闻文章',
            'table_name' => 'posts_news',
        ]);

        $category = Category::create([
            'name' => '新闻中心',
            'slug' => 'news',
            'model_id' => $model->id,
        ]);

        $this->actingAs($user)->post(route('member.posts.store'), [
            'title' => '会员前台上传封面',
            'summary' => '测试前后台媒体联通',
            'content' => '这里是正文。',
            'category_id' => $category->id,
            'cover_upload' => UploadedFile::fake()->image('member-cover.png', 1200, 800),
            'status' => 'pending',
        ])->assertRedirect(route('member.dashboard'));

        $attachment = Attachment::query()->latest('id')->firstOrFail();
        $mediaFile = MediaFile::query()->findOrFail($attachment->media_file_id);

        $this->assertSame($user->id, $mediaFile->uploaded_by_user_id);

        Livewire::actingAs($user);

        Livewire::test(MediaBrowser::class)
            ->set('perPage', 'all')
            ->set('search', $mediaFile->name)
            ->assertSee($mediaFile->name)
            ->assertCount('items', 1);
    }
}
