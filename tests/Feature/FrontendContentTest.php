<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Comment;
use App\Models\ContentModel;
use App\Models\Post;
use App\Models\PostStatistic;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Slimani\MediaManager\Models\File;
use Tests\TestCase;

class FrontendContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_page_lists_published_posts(): void
    {
        [$category, $post] = $this->createPublishedPost();

        $this->get("/categories/{$category->slug}")
            ->assertOk()
            ->assertSee($category->name)
            ->assertSee($post->title);
    }

    public function test_post_detail_page_shows_tags_attachments_and_approved_comments(): void
    {
        [, $post] = $this->createPublishedPost();

        $attachment = $this->createMediaFile($post->user_id, 'manual.pdf', 'application/pdf', 'PDF content');
        $coverAttachment = $this->createMediaFile($post->user_id, 'cover.png', 'image/png', $this->generateTinyPng());

        $post->detail()->create([
            'content' => '<p>这里是文章正文。</p>',
            'custom_fields' => [
                'seo_title' => '更适合前台展示的 SEO 标题',
                'summary' => '这是文章摘要，用来验证前台内容运营字段展示。',
            ],
        ]);

        $post->coverMediaFiles()->sync([
            $coverAttachment->id => ['collection' => 'cover'],
        ]);

        $post->attachmentMediaFiles()->sync([
            $attachment->id => ['collection' => 'attachments'],
        ]);

        $post->tags()->create([
            'name' => 'Laravel',
            'slug' => 'laravel',
            'count' => 1,
        ]);

        Comment::create([
            'user_id' => $post->user_id,
            'post_id' => $post->id,
            'content' => '这是一条已审核评论',
            'status' => 'approved',
        ]);

        Comment::create([
            'user_id' => $post->user_id,
            'post_id' => $post->id,
            'content' => '这是一条待审核评论',
            'status' => 'pending',
        ]);

        $this->get("/posts/{$post->slug}")
            ->assertOk()
            ->assertSee($post->title)
            ->assertSee('这是文章摘要，用来验证前台内容运营字段展示。')
            ->assertSee('Laravel')
            ->assertSee('manual.pdf')
            ->assertSee('这是一条已审核评论')
            ->assertDontSee('这是一条待审核评论');
    }

    public function test_authenticated_user_can_submit_frontend_comment(): void
    {
        [, $post] = $this->createPublishedPost();

        $this->actingAs($post->user)
            ->post("/posts/{$post->slug}/comments", [
                'body' => '这是前台提交的新评论',
            ])
            ->assertRedirect("/posts/{$post->slug}");

        $this->assertDatabaseHas('comments', [
            'commentable_type' => Post::class,
            'commentable_id' => $post->id,
            'author_type' => User::class,
            'author_id' => $post->user_id,
            'body' => '这是前台提交的新评论',
            'status' => 'pending',
        ]);
    }

    public function test_authenticated_user_can_submit_frontend_reply(): void
    {
        [, $post] = $this->createPublishedPost();

        $parentComment = Comment::create([
            'user_id' => $post->user_id,
            'post_id' => $post->id,
            'content' => '这是主评论',
            'status' => 'approved',
        ]);

        $this->actingAs($post->user)
            ->post("/posts/{$post->slug}/comments", [
                'body' => '这是前台提交的回复',
                'parent_id' => $parentComment->id,
            ])
            ->assertRedirect("/posts/{$post->slug}");

        $this->assertDatabaseHas('comments', [
            'commentable_type' => Post::class,
            'commentable_id' => $post->id,
            'author_type' => User::class,
            'author_id' => $post->user_id,
            'post_id' => $post->id,
            'parent_id' => $parentComment->id,
            'body' => '这是前台提交的回复',
            'status' => 'pending',
        ]);
    }

    public function test_tag_archive_lists_related_posts(): void
    {
        [, $post] = $this->createPublishedPost();

        $tag = Tag::create([
            'name' => '门户专题',
            'slug' => 'portal-topic',
            'count' => 1,
        ]);

        $post->tags()->sync([$tag->id]);

        $this->get("/tags/{$tag->slug}")
            ->assertOk()
            ->assertSee('# '.$tag->name, escape: false)
            ->assertSee($post->title);
    }

    public function test_search_page_can_find_posts_by_keyword(): void
    {
        [, $post] = $this->createPublishedPost();

        $post->detail()->create([
            'content' => '<p>这里记录搜索结果页的正文片段。</p>',
            'custom_fields' => [
                'summary' => '搜索页测试摘要',
            ],
        ]);

        $this->get('/search?q=frontend')
            ->assertOk()
            ->assertSee('frontend')
            ->assertSee($post->title);
    }

    /**
     * @return array{Category, Post}
     */
    private function createPublishedPost(): array
    {
        $user = User::factory()->create();

        $category = Category::create([
            'name' => '资讯中心',
            'slug' => 'news-center',
            'sort_order' => 1,
            'level' => 0,
        ]);

        $contentModel = ContentModel::create([
            'name' => '文章模型',
            'table_name' => 'posts_article',
        ]);

        $post = Post::create([
            'title' => '帝国 CMS 前台骨架文章',
            'slug' => 'empire-cms-frontend-post',
            'category_id' => $category->id,
            'model_id' => $contentModel->id,
            'user_id' => $user->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        PostStatistic::create([
            'post_id' => $post->id,
            'views' => 12,
            'likes' => 3,
            'comments_count' => 0,
        ]);

        return [$category, $post];
    }

    private function createMediaFile(int $userId, string $filename, string $mimeType, string $content): File
    {
        $file = File::create([
            'uploaded_by_user_id' => $userId,
            'name' => pathinfo($filename, PATHINFO_FILENAME),
            'mime_type' => $mimeType,
            'extension' => pathinfo($filename, PATHINFO_EXTENSION),
            'size' => strlen($content),
        ]);

        $media = $file->addMediaFromString($content)
            ->usingFileName($filename)
            ->toMediaCollection('default');

        $file->update([
            'size' => $media->size,
            'mime_type' => $media->mime_type,
            'extension' => $media->extension,
        ]);

        return $file->fresh();
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
}
