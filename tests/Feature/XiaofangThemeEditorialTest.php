<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ContentModel;
use App\Models\Event;
use App\Models\MemberGroup;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XiaofangThemeEditorialTest extends TestCase
{
    use RefreshDatabase;

    public function test_xiaofang_homepage_renders_editorial_modules_and_event_drawer_trigger(): void
    {
        $this->activateTheme('xiaofang');

        [$category, $model] = $this->createContentBase();
        $authorA = $this->createAuthor('linmei', 'Lin Mei');
        $authorB = $this->createAuthor('sunhao', 'Sun Hao');

        $headline = $this->createPublishedPost($authorA, $category, $model, 'architects-modern-taste', 'The Architects of Modern Taste', [
            'published_at' => now()->subDays(3),
            'is_headline' => true,
        ]);
        $featuredOne = $this->createPublishedPost($authorA, $category, $model, 'creative-community', 'Building a Creative Community', [
            'published_at' => now()->subDay(),
            'is_featured' => true,
        ]);
        $featuredTwo = $this->createPublishedPost($authorB, $category, $model, 'daily-creativity', 'Harnessing the Power of Daily Creativity', [
            'published_at' => now()->subHours(12),
            'is_featured' => true,
        ]);
        $recommended = $this->createPublishedPost($authorB, $category, $model, 'recommended-strip-story', 'A Recommended Strip Story', [
            'published_at' => now()->subHours(6),
            'is_recommended' => true,
        ]);
        $this->createPublishedPost($authorB, $category, $model, 'newest-regular-story', 'A Regular Story That Should Not Replace Headline', [
            'published_at' => now(),
        ]);

        Event::create([
            'title' => 'Editorial Salon',
            'slug' => 'editorial-salon',
            'status' => 'registration-open',
            'location' => 'Shanghai',
            'is_paid' => false,
            'price' => 0,
            'starts_at' => now()->addDays(7),
            'ends_at' => now()->addDays(7)->addHours(3),
            'registration_opens_at' => now()->subDay(),
            'registration_closes_at' => now()->addDays(5),
            'capacity' => 30,
            'summary' => 'A live event for the new theme.',
            'content' => 'Event content',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertViewHas('leadPost', fn (?Post $post): bool => $post?->is($headline))
            ->assertViewHas('featuredPosts', fn ($posts): bool => collect($posts->take(2)->pluck('id')->all())->sort()->values()->all() === collect([$featuredOne->id, $featuredTwo->id])->sort()->values()->all())
            ->assertSee('A Recommended Strip Story')
            ->assertSee('Latest Articles')
            ->assertSee('Meet Our Authors')
            ->assertSee('Editorial Salon')
            ->assertSee('data-xf-events-trigger', false)
            ->assertSee('Lin Mei');
    }

    public function test_xiaofang_author_page_shows_only_published_posts(): void
    {
        $this->activateTheme('xiaofang');

        [$category, $model] = $this->createContentBase();
        $author = $this->createAuthor('author-page', 'Pedro Silva', 'Design Writer', 'Writes about space and contemporary taste.');

        $this->createPublishedPost($author, $category, $model, 'published-author-story', 'Published Author Story');

        Post::create([
            'title' => 'Draft Story',
            'slug' => 'draft-story',
            'category_id' => $category->id,
            'model_id' => $model->id,
            'user_id' => $author->id,
            'status' => 'draft',
            'published_at' => null,
        ])->detail()->create([
            'content' => '<p>Draft</p>',
            'custom_fields' => [
                'summary' => 'Draft summary',
            ],
        ]);

        $this->get(route('authors.show', $author->username))
            ->assertOk()
            ->assertSee('Pedro Silva')
            ->assertSee('Writes about space and contemporary taste.')
            ->assertSee('Published Author Story')
            ->assertDontSee('Draft Story');
    }

    public function test_xiaofang_post_page_displays_author_card_and_author_link(): void
    {
        $this->activateTheme('xiaofang');

        [$category, $model] = $this->createContentBase();
        $author = $this->createAuthor('post-author', 'John Doe', 'Publisher', 'Publisher with passion for functional design and minimalism.');

        $post = $this->createPublishedPost($author, $category, $model, 'post-with-author-card', 'Post With Author Card');
        $this->createPublishedPost($author, $category, $model, 'second-author-story', 'Second Author Story');

        $this->get(route('posts.show', $post->slug))
            ->assertOk()
            ->assertSee('Share this post')
            ->assertSee('Written by')
            ->assertSee('John Doe')
            ->assertSee(route('authors.show', $author->username), false)
            ->assertSee('Second Author Story');
    }

    public function test_xiaofang_post_page_can_render_legacy_html_content_without_crashing(): void
    {
        $this->activateTheme('xiaofang');

        [$category, $model] = $this->createContentBase();
        $author = $this->createAuthor('legacy-author', 'Legacy Author');

        $post = Post::create([
            'title' => 'Legacy Content Story',
            'slug' => 'legacy-content-story',
            'category_id' => $category->id,
            'model_id' => $model->id,
            'user_id' => $author->id,
            'status' => 'published',
            'published_at' => now(),
            'is_headline' => false,
            'is_featured' => false,
            'is_recommended' => false,
        ]);

        $post->detail()->create([
            'content' => '<p>Legacy HTML body</p>',
            'custom_fields' => [
                'summary' => 'Legacy summary',
            ],
        ]);

        $this->get(route('posts.show', $post->slug))
            ->assertOk()
            ->assertSee('Legacy HTML body')
            ->assertSee('Written by');
    }

    private function activateTheme(string $theme): void
    {
        $settings = SiteSetting::current();
        $businessSettings = $settings->business_settings ?? [];
        $businessSettings['active_theme'] = $theme;

        $settings->update([
            'business_settings' => $businessSettings,
        ]);
    }

    private function createAuthor(string $username, string $displayName, string $headline = 'Feature Writer', string $bio = 'Author bio'): User
    {
        $group = MemberGroup::query()->firstOrCreate([
            'name' => '测试会员组',
        ], [
            'min_points' => 0,
            'max_points' => 999,
        ]);

        return User::create([
            'username' => $username,
            'display_name' => $displayName,
            'headline' => $headline,
            'bio' => $bio,
            'email' => $username.'@example.com',
            'password_hash' => 'secret123',
            'group_id' => $group->id,
            'status' => 'active',
        ]);
    }

    private function createContentBase(): array
    {
        $model = ContentModel::create([
            'name' => 'Editorial',
            'table_name' => 'posts_news',
        ]);

        $category = Category::create([
            'name' => 'Design',
            'slug' => 'design',
            'model_id' => $model->id,
        ]);

        return [$category, $model];
    }

    private function createPublishedPost(User $author, Category $category, ContentModel $model, string $slug, string $title, array $attributes = []): Post
    {
        $post = Post::create(array_merge([
            'title' => $title,
            'slug' => $slug,
            'category_id' => $category->id,
            'model_id' => $model->id,
            'user_id' => $author->id,
            'status' => 'published',
            'published_at' => now()->subDay(),
            'is_headline' => false,
            'is_featured' => false,
            'is_recommended' => false,
        ], $attributes));

        $post->detail()->create([
            'content' => '<p>This is editorial content for '.$title.'.</p>',
            'custom_fields' => [
                'summary' => 'Summary for '.$title,
                'cover_image_url' => 'https://cdn.example.com/'.$slug.'.jpg',
            ],
        ]);

        return $post;
    }
}
