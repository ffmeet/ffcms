<?php

namespace Database\Seeders;

use App\Models\Attachment;
use App\Models\Category;
use App\Models\Comment;
use App\Models\ContentModel;
use App\Models\MemberGroup;
use App\Models\Post;
use App\Models\PostStatistic;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Slimani\MediaManager\Models\File;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $defaultGroup = MemberGroup::query()->firstOrCreate(
            ['name' => '默认会员'],
            [
                'min_points' => 0,
                'max_points' => 9999,
                'permissions' => ['site.access' => true],
            ],
        );

        User::query()->firstOrCreate(
            ['username' => 'testuser'],
            [
                'email' => 'test@example.com',
                'password_hash' => '123456',
                'group_id' => $defaultGroup->id,
                'status' => 'active',
            ],
        );

        $adminGroup = MemberGroup::query()->firstOrCreate(
            ['name' => '管理员'],
            [
                'min_points' => 0,
                'max_points' => 999999,
                'permissions' => ['admin.access' => true],
            ],
        );

        $admin = User::query()->firstOrCreate(
            ['username' => 'admin'],
            [
                'email' => 'admin@example.com',
                'password_hash' => '789789',
                'group_id' => $adminGroup->id,
                'status' => 'active',
            ],
        );

        $demoMembers = collect([
            ['username' => 'linmei', 'email' => 'linmei@example.com'],
            ['username' => 'sunhao', 'email' => 'sunhao@example.com'],
            ['username' => 'chenyu', 'email' => 'chenyu@example.com'],
            ['username' => 'zhaoqi', 'email' => 'zhaoqi@example.com'],
            ['username' => 'wuyue', 'email' => 'wuyue@example.com'],
        ])->map(fn (array $member): User => User::query()->firstOrCreate(
            ['username' => $member['username']],
            [
                'email' => $member['email'],
                'password_hash' => '123456',
                'group_id' => $defaultGroup->id,
                'status' => 'active',
            ],
        ));

        $newsCategory = Category::query()->firstOrCreate(
            ['slug' => 'news'],
            [
                'name' => '新闻中心',
                'description' => '站点新闻与公告内容',
                'sort_order' => 1,
                'level' => 0,
            ],
        );

        $articleModel = ContentModel::query()->updateOrCreate(
            ['table_name' => 'posts_news'],
            [
                'name' => '新闻文章',
                'field_config' => [
                    'seo_title' => 'string',
                    'summary' => 'text',
                    'source' => 'string',
                    'keywords' => 'text',
                    'is_featured' => 'boolean',
                ],
            ],
        );

        $cmsTag = Tag::query()->firstOrCreate(
            ['slug' => 'cms'],
            ['name' => 'CMS', 'count' => 1],
        );

        $laravelTag = Tag::query()->firstOrCreate(
            ['slug' => 'laravel'],
            ['name' => 'Laravel', 'count' => 1],
        );

        $post = Post::query()->firstOrCreate(
            ['slug' => 'empire-cms-rebuild-kickoff'],
            [
                'title' => '帝国 CMS 重构启动',
                'category_id' => $newsCategory->id,
                'model_id' => $articleModel->id,
                'user_id' => $admin->id,
                'status' => 'published',
                'published_at' => now(),
            ],
        );

        $post->detail()->updateOrCreate(
            ['post_id' => $post->id],
            [
                'content' => '这是第一篇演示文章，用来验证后台发布、评论、标签和统计流程是否连通。',
                'custom_fields' => [
                    'seo_title' => '帝国 CMS 重构启动',
                    'summary' => '用于验证后台流程的演示内容',
                ],
            ],
        );

        $post->tags()->syncWithoutDetaching([$cmsTag->id, $laravelTag->id]);

        $coverMedia = $this->createImageFile($admin->id, 'launch-cover', 'launch-cover.png');
        $attachmentMedia = $this->createDocumentFile($admin->id, 'project-brief', 'project-brief.pdf');

        foreach ([
            ['name' => 'avatar-admin', 'filename' => 'avatar-admin.png', 'user_id' => $admin->id],
            ['name' => 'avatar-linmei', 'filename' => 'avatar-linmei.png', 'user_id' => $demoMembers[0]->id],
            ['name' => 'avatar-sunhao', 'filename' => 'avatar-sunhao.png', 'user_id' => $demoMembers[1]->id],
            ['name' => 'avatar-chenyu', 'filename' => 'avatar-chenyu.png', 'user_id' => $demoMembers[2]->id],
            ['name' => 'comment-scene-1', 'filename' => 'comment-scene-1.png', 'user_id' => $admin->id],
            ['name' => 'comment-scene-2', 'filename' => 'comment-scene-2.png', 'user_id' => $admin->id],
        ] as $image) {
            $this->createImageFile($image['user_id'], $image['name'], $image['filename']);
        }

        $post->coverMediaFiles()->sync([
            $coverMedia->id => ['collection' => 'cover'],
        ]);

        $post->attachmentMediaFiles()->sync([
            $attachmentMedia->id => ['collection' => 'attachments'],
        ]);

        $commentOne = $this->upsertComment(
            $post,
            $admin,
            '后台评论模块已经接入，后续可以继续扩展审核流和嵌套回复。',
            'approved',
        );

        $commentTwo = $this->upsertComment(
            $post,
            $demoMembers[0],
            '首页、栏目页和文章详情已经串起来了，现在前台阅读体验顺很多。',
            'approved',
        );

        $this->upsertComment(
            $post,
            $demoMembers[1],
            '我刚从前台登录提交了一条评论，审核流程是通的，体验也比之前清楚。',
            'approved',
            $commentTwo->id,
        );

        $this->upsertComment(
            $post,
            $demoMembers[2],
            '如果后面再补标签聚合页和搜索排序，这套门户化内容发现就更完整了。',
            'approved',
        );

        $this->upsertComment(
            $post,
            $demoMembers[3],
            '这条是待审核示例评论，用来验证后台审核动作和前台隐藏逻辑。',
            'pending',
        );

        $this->upsertComment(
            $post,
            $demoMembers[4],
            '我会继续从会员中心和前台路径测试一遍，看看评论与文章统计同步是否稳定。',
            'pending',
            $commentOne->id,
        );

        $post->syncCommentStatistics();

        PostStatistic::query()->updateOrCreate(
            ['post_id' => $post->id],
            [
                'views' => 128,
                'likes' => 12,
                'comments_count' => $post->fresh()->statistics?->comments_count ?? 0,
            ],
        );

        Attachment::query()->firstOrCreate(
            ['filepath' => 'uploads/demo/launch-cover.jpg'],
            [
                'user_id' => $admin->id,
                'filename' => 'launch-cover.jpg',
                'mime_type' => 'image/jpeg',
                'size' => 245760,
            ],
        );
    }

    private function generateTinyPng(): string
    {
        $image = imagecreatetruecolor(2, 2);
        $background = imagecolorallocate($image, 245, 158, 11);
        imagefill($image, 0, 0, $background);

        ob_start();
        imagepng($image);
        $contents = ob_get_clean();

        imagedestroy($image);

        return $contents ?: '';
    }

    private function createImageFile(int $userId, string $name, string $filename): File
    {
        $file = File::query()->firstOrCreate(
            ['name' => $name],
            [
                'uploaded_by_user_id' => $userId,
                'mime_type' => 'image/png',
                'extension' => 'png',
                'size' => 0,
            ],
        );

        if (! $file->getFirstMedia('default')) {
            $media = $file->addMediaFromString($this->generateTinyPng())
                ->usingFileName($filename)
                ->toMediaCollection('default');

            $file->update([
                'size' => $media->size,
                'mime_type' => $media->mime_type,
                'extension' => $media->extension,
            ]);
        }

        return $file->fresh();
    }

    private function createDocumentFile(int $userId, string $name, string $filename): File
    {
        $file = File::query()->firstOrCreate(
            ['name' => $name],
            [
                'uploaded_by_user_id' => $userId,
                'mime_type' => 'application/pdf',
                'extension' => 'pdf',
                'size' => 0,
            ],
        );

        if (! $file->getFirstMedia('default')) {
            $media = $file->addMediaFromString('seed-pdf-content')
                ->usingFileName($filename)
                ->toMediaCollection('default');

            $file->update([
                'size' => $media->size,
                'mime_type' => $media->mime_type,
                'extension' => $media->extension,
            ]);
        }

        return $file->fresh();
    }

    private function upsertComment(Post $post, User $user, string $body, string $status, ?int $parentId = null): Comment
    {
        return Comment::query()->updateOrCreate(
            [
                'commentable_type' => Post::class,
                'commentable_id' => $post->id,
                'body' => $body,
            ],
            [
                'post_id' => $post->id,
                'user_id' => $user->id,
                'author_type' => User::class,
                'author_id' => $user->id,
                'content' => $body,
                'status' => $status,
                'parent_id' => $parentId,
            ],
        );
    }
}
