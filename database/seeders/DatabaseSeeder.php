<?php

namespace Database\Seeders;

use App\Models\Attachment;
use App\Models\Category;
use App\Models\Comment;
use App\Models\ContentModel;
use App\Models\MemberGroup;
use App\Models\Post;
use App\Models\PostStatistic;
use App\Models\SiteSetting;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Slimani\MediaManager\Models\File;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        [$defaultGroup, $adminGroup] = $this->seedMemberGroups();
        [$articleModel, $flashModel, $categories] = $this->seedCategoriesAndModels();
        $this->seedSiteSettings();
        $tags = $this->seedTags();

        if (! $this->shouldSeedDemoContent()) {
            return;
        }

        $users = $this->seedUsers($defaultGroup, $adminGroup);
        $posts = $this->seedEditorialPosts($users, $categories, $tags, $articleModel, $flashModel);
        $this->seedCommentsAndMedia($posts, $users);

        $this->call(CommerceDemoSeeder::class);
    }

    private function shouldSeedDemoContent(): bool
    {
        return App::environment(['local', 'testing'])
            || filter_var(env('FFMEET_ALLOW_DEMO_SEED', false), FILTER_VALIDATE_BOOL);
    }

    private function seedMemberGroups(): array
    {
        $defaultGroup = MemberGroup::query()->updateOrCreate(
            ['name' => '默认会员'],
            [
                'min_points' => 0,
                'max_points' => 9999,
                'permissions' => [
                    'site.access' => true,
                    'member.center' => true,
                    'shop.access' => true,
                    'events.access' => true,
                ],
            ],
        );

        $adminGroup = MemberGroup::query()->updateOrCreate(
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

        return [$defaultGroup, $adminGroup];
    }

    private function seedUsers(MemberGroup $defaultGroup, MemberGroup $adminGroup): array
    {
        $userDefinitions = [
            'admin' => [
                'email' => 'admin@example.com',
                'password_hash' => '789789',
                'group_id' => $adminGroup->id,
                'display_name' => 'Editorial Desk',
                'headline' => '编辑部',
                'bio' => '负责整站专题策划、内容节奏和主题实验，把前台体验组织成一份真正可阅读的刊物。',
                'social_links' => [
                    ['label' => 'Instagram', 'url' => 'https://example.com/editorial-desk'],
                ],
            ],
            'linmei' => [
                'email' => 'linmei@example.com',
                'password_hash' => '123456',
                'group_id' => $defaultGroup->id,
                'display_name' => 'Lin Mei',
                'headline' => 'Culture Editor',
                'bio' => '关注城市文化、生活方式与影像叙事，把人、空间和审美趋势写进日常文章。',
                'social_links' => [
                    ['label' => 'Instagram', 'url' => 'https://example.com/linmei'],
                ],
            ],
            'sunhao' => [
                'email' => 'sunhao@example.com',
                'password_hash' => '123456',
                'group_id' => $defaultGroup->id,
                'display_name' => 'Sun Hao',
                'headline' => 'Design Writer',
                'bio' => '长期记录设计趋势、创作者方法与数字产品之间的微妙关系。',
            ],
            'chenyu' => [
                'email' => 'chenyu@example.com',
                'password_hash' => '123456',
                'group_id' => $defaultGroup->id,
                'display_name' => 'Chen Yu',
                'headline' => 'Politics Columnist',
                'bio' => '关注公共议题、政策风向与城市更新，把宏观话题写成更有可读性的深度报道。',
            ],
            'zhaoqi' => [
                'email' => 'zhaoqi@example.com',
                'password_hash' => '123456',
                'group_id' => $defaultGroup->id,
                'display_name' => 'Zhao Qi',
                'headline' => 'Lifestyle Reporter',
                'bio' => '偏爱写人物、居住方式与日常消费决策中的细微变化。',
            ],
            'wuyue' => [
                'email' => 'wuyue@example.com',
                'password_hash' => '123456',
                'group_id' => $defaultGroup->id,
                'display_name' => 'Wu Yue',
                'headline' => 'Ideas & Books',
                'bio' => '用书写和访谈梳理创意工作背后的方法论与个体经验。',
            ],
            'anran' => [
                'email' => 'anran@example.com',
                'password_hash' => '123456',
                'group_id' => $defaultGroup->id,
                'display_name' => 'An Ran',
                'headline' => 'Architecture Observer',
                'bio' => '关注建筑、空间和材料如何塑造当代生活的视觉秩序与情绪。',
            ],
            'yiyang' => [
                'email' => 'yiyang@example.com',
                'password_hash' => '123456',
                'group_id' => $defaultGroup->id,
                'display_name' => 'Yi Yang',
                'headline' => 'Technology Feature Writer',
                'bio' => '把科技趋势写成带有人物与场景感的长文，尽量避免冷冰冰的资讯堆砌。',
            ],
            'testuser' => [
                'email' => 'test@example.com',
                'password_hash' => '123456',
                'group_id' => $defaultGroup->id,
                'display_name' => 'Test User',
                'headline' => 'Community Member',
                'bio' => '默认测试会员，用于验证前台会员中心和互动链路。',
            ],
        ];

        return collect($userDefinitions)
            ->mapWithKeys(fn (array $definition, string $username): array => [
                $username => User::query()->updateOrCreate(
                    ['username' => $username],
                    array_merge($definition, ['status' => 'active']),
                ),
            ])
            ->all();
    }

    private function seedCategoriesAndModels(): array
    {
        $articleModel = ContentModel::query()->updateOrCreate(
            ['table_name' => 'posts_news'],
            [
                'name' => 'Editorial Story',
                'field_config' => [
                    'seo_title' => 'string',
                    'summary' => 'text',
                    'source' => 'string',
                    'keywords' => 'text',
                ],
            ],
        );

        $flashModel = ContentModel::query()->updateOrCreate(
            ['table_name' => 'posts_flash'],
            [
                'name' => 'Flash Story',
                'field_config' => [
                    'summary' => 'text',
                ],
            ],
        );

        $categories = collect([
            'design' => ['name' => 'Design', 'description' => '设计、建筑与创意工作方式', 'sort_order' => 1],
            'politics' => ['name' => 'Politics', 'description' => '公共议题、政策和城市更新观察', 'sort_order' => 2],
            'lifestyle' => ['name' => 'Lifestyle', 'description' => '居住方式、日常消费与审美趋势', 'sort_order' => 3],
            'culture' => ['name' => 'Culture', 'description' => '文化人物、读物与展览现场', 'sort_order' => 4],
            'technology' => ['name' => 'Technology', 'description' => '科技趋势与产品叙事', 'sort_order' => 5],
            'flash' => ['name' => 'Flash', 'description' => '快讯、动态与短更新', 'sort_order' => 6],
        ])->mapWithKeys(function (array $definition, string $slug) use ($articleModel, $flashModel): array {
            $modelId = $slug === 'flash' ? $flashModel->id : $articleModel->id;

            return [
                $slug => Category::query()->updateOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => $definition['name'],
                        'description' => $definition['description'],
                        'model_id' => $modelId,
                        'sort_order' => $definition['sort_order'],
                        'level' => 0,
                    ],
                ),
            ];
        })->all();

        return [$articleModel, $flashModel, $categories];
    }

    private function seedTags(): array
    {
        return collect([
            'architecture' => 'Architecture',
            'design' => 'Design',
            'politics' => 'Politics',
            'lifestyle' => 'Lifestyle',
            'culture' => 'Culture',
            'technology' => 'Technology',
            'creative-work' => 'Creative Work',
            'interview' => 'Interview',
            'journal' => 'Journal',
            'city' => 'City',
        ])->mapWithKeys(fn (string $name, string $slug): array => [
            $slug => Tag::query()->updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'count' => 0],
            ),
        ])->all();
    }

    private function seedEditorialPosts(array $users, array $categories, array $tags, ContentModel $articleModel, ContentModel $flashModel): array
    {
        $definitions = [
            [
                'slug' => 'empire-cms-rebuild-kickoff',
                'title' => 'The Architects of Modern Taste',
                'author' => 'anran',
                'category' => 'design',
                'summary' => 'Why do certain visual decisions quietly become the language of an era? We begin with the spaces, objects and people shaping contemporary taste.',
                'cover' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=1600&q=80',
                'tags' => ['architecture', 'culture', 'journal'],
                'days' => 10,
            ],
            [
                'slug' => 'state-department-cuts-and-diplomatic-influence',
                'title' => 'State Department Under the Knife: Potential Funding Cuts and Diplomatic Influence',
                'author' => 'chenyu',
                'category' => 'politics',
                'summary' => 'Field reporting and policy reading meet in a cleaner long-form story about how budget priorities shape diplomacy.',
                'cover' => 'https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=1600&q=80',
                'tags' => ['politics', 'city', 'journal'],
                'days' => 9,
            ],
            [
                'slug' => 'building-a-creative-community',
                'title' => 'Building a Creative Community',
                'author' => 'wuyue',
                'category' => 'culture',
                'summary' => 'A supportive creative community is less a slogan than a lived infrastructure for work, recovery and momentum.',
                'cover' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=1600&q=80',
                'tags' => ['culture', 'creative-work', 'interview'],
                'days' => 8,
            ],
            [
                'slug' => 'home-harmony-modern-living',
                'title' => 'Home Harmony: Creating Spaces for Modern Living',
                'author' => 'zhaoqi',
                'category' => 'lifestyle',
                'summary' => 'Your everyday environment doesn’t need to be maximal to feel warm. It needs rhythm, texture and better editing.',
                'cover' => 'https://images.unsplash.com/photo-1494526585095-c41746248156?auto=format&fit=crop&w=1600&q=80',
                'tags' => ['lifestyle', 'architecture', 'journal'],
                'days' => 7,
            ],
            [
                'slug' => 'parenting-in-the-digital-age',
                'title' => 'Parenting in the Digital Age: Balancing Connection and Concerns',
                'author' => 'linmei',
                'category' => 'lifestyle',
                'summary' => 'Screens and schedules reshape family attention. This story follows how parents are redrawing boundaries without giving up connection.',
                'cover' => 'https://images.unsplash.com/photo-1519345182560-3f2917c472ef?auto=format&fit=crop&w=1600&q=80',
                'tags' => ['lifestyle', 'culture'],
                'days' => 6,
            ],
            [
                'slug' => 'wellness-wonders-modern-approaches',
                'title' => 'Wellness Wonders: Exploring Modern Approaches to Well-being',
                'author' => 'wuyue',
                'category' => 'lifestyle',
                'summary' => 'Beyond the buzzword, well-being is becoming a story about ritual, recovery and the design of ordinary time.',
                'cover' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1600&q=80',
                'tags' => ['lifestyle', 'creative-work'],
                'days' => 5,
            ],
            [
                'slug' => 'mindful-consumption-matters',
                'title' => 'Mindful Consumption: Making Choices That Matter',
                'author' => 'sunhao',
                'category' => 'culture',
                'summary' => 'Shopping habits, brand language and taste now overlap with identity more than ever. The question is how to buy more deliberately.',
                'cover' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=1600&q=80',
                'tags' => ['culture', 'lifestyle'],
                'days' => 4,
            ],
            [
                'slug' => 'bezos-blueprint-opinion-pages',
                'title' => 'Bezos’ Blueprint: Shaping the Washington Post’s Opinion Pages',
                'author' => 'chenyu',
                'category' => 'politics',
                'summary' => 'Media ownership is once again a story about institutional direction, editorial pressure and the emotional texture of public discourse.',
                'cover' => 'https://images.unsplash.com/photo-1529107386315-e1a2ed48a620?auto=format&fit=crop&w=1600&q=80',
                'tags' => ['politics', 'culture', 'journal'],
                'days' => 3,
            ],
            [
                'slug' => 'openai-unveils-gpt4-dot1-ai-power',
                'title' => 'OpenAI Unveils GPT-4.1: The Next Leap in AI Power',
                'author' => 'yiyang',
                'category' => 'technology',
                'summary' => 'The AI story is no longer only about bigger models. It is about what the new interface between intelligence and workflow now feels like.',
                'cover' => 'https://images.unsplash.com/photo-1677442136019-21780ecad995?auto=format&fit=crop&w=1600&q=80',
                'tags' => ['technology', 'creative-work'],
                'days' => 2,
                'is_featured' => true,
            ],
            [
                'slug' => 'how-a-cartoon-defined-global-perspective',
                'title' => '9th Ave’s World: How a Cartoon Defined Global Perspective',
                'author' => 'linmei',
                'category' => 'culture',
                'summary' => 'Some images compress a whole worldview into one frame. This essay revisits why one iconic illustration still travels so well.',
                'cover' => 'https://images.unsplash.com/photo-1547891654-e66ed7ebb968?auto=format&fit=crop&w=1600&q=80',
                'tags' => ['culture', 'journal'],
                'days' => 1,
                'is_featured' => true,
            ],
            [
                'slug' => 'storm-center-and-a-certain-future',
                'title' => '雷军：在风暴中心，寻找那个确定的未来',
                'author' => 'admin',
                'category' => 'technology',
                'summary' => '一条更适合首页横向窄条的推荐内容，用来承接头条下方的阅读延伸。',
                'cover' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1600&q=80',
                'tags' => ['technology', 'journal'],
                'days' => 1,
                'is_recommended' => true,
            ],
            [
                'slug' => 'finding-inspiration-in-everyday-life',
                'title' => 'Finding Inspiration in Everyday Life',
                'author' => 'sunhao',
                'category' => 'design',
                'summary' => 'Inspiration rarely arrives as a lightning strike. It more often shows up as a repeatable practice of noticing.',
                'cover' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=1600&q=80',
                'tags' => ['design', 'creative-work'],
                'days' => 0,
                'is_headline' => true,
            ],
            [
                'slug' => 'creative-blocks-and-how-to-overcome-them',
                'title' => 'How to Overcome Creative Blocks',
                'author' => 'wuyue',
                'category' => 'flash',
                'summary' => 'A short editorial note on what to do when ideas stop arriving on time.',
                'cover' => 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?auto=format&fit=crop&w=1600&q=80',
                'tags' => ['creative-work', 'journal'],
                'days' => 0,
            ],
        ];

        $posts = [];

        foreach ($definitions as $index => $definition) {
            $modelId = $definition['category'] === 'flash' ? $flashModel->id : $articleModel->id;
            $publishedAt = now()->subDays($definition['days'])->subHours($index);

            $post = Post::query()->updateOrCreate(
                ['slug' => $definition['slug']],
                [
                    'title' => $definition['title'],
                    'category_id' => $categories[$definition['category']]->id,
                    'model_id' => $modelId,
                    'user_id' => $users[$definition['author']]->id,
                    'status' => 'published',
                    'published_at' => $publishedAt,
                    'is_headline' => (bool) ($definition['is_headline'] ?? false),
                    'is_featured' => (bool) ($definition['is_featured'] ?? false),
                    'is_recommended' => (bool) ($definition['is_recommended'] ?? false),
                ],
            );

            $post->detail()->updateOrCreate(
                ['post_id' => $post->id],
                [
                    'content' => $this->editorialBody($definition['summary'], $definition['title']),
                    'custom_fields' => [
                        'seo_title' => $definition['title'],
                        'summary' => $definition['summary'],
                        'cover_image_url' => $definition['cover'],
                    ],
                ],
            );

            $post->tags()->sync(
                collect($definition['tags'])
                    ->map(fn (string $slug) => $tags[$slug]->id)
                    ->all(),
            );

            PostStatistic::query()->updateOrCreate(
                ['post_id' => $post->id],
                [
                    'views' => 120 + ($index * 37),
                    'likes' => 8 + ($index * 3),
                    'comments_count' => 0,
                ],
            );

            $posts[$definition['slug']] = $post->fresh(['detail', 'statistics', 'tags', 'user']);
        }

        foreach ($tags as $tag) {
            $tag->update(['count' => $tag->posts()->count()]);
        }

        return $posts;
    }

    private function seedCommentsAndMedia(array $posts, array $users): void
    {
        $launchPost = $posts['empire-cms-rebuild-kickoff'];

        $coverMedia = $this->createImageFile($users['admin']->id, 'launch-cover', 'launch-cover.png');
        $attachmentMedia = $this->createDocumentFile($users['admin']->id, 'editorial-manifesto', 'editorial-manifesto.pdf');

        $launchPost->coverMediaFiles()->sync([
            $coverMedia->id => ['collection' => 'cover'],
        ]);

        $launchPost->attachmentMediaFiles()->sync([
            $attachmentMedia->id => ['collection' => 'attachments'],
        ]);

        Attachment::query()->updateOrCreate(
            ['filepath' => 'uploads/demo/editorial-manifesto.pdf'],
            [
                'user_id' => $users['admin']->id,
                'filename' => 'editorial-manifesto.pdf',
                'mime_type' => 'application/pdf',
                'size' => 10240,
            ],
        );

        $commentOne = $this->upsertComment(
            $launchPost,
            $users['admin'],
            '新版主题已经转向刊物式编排，首页、作者页和详情页的阅读节奏会明显更统一。',
            'approved',
        );

        $commentTwo = $this->upsertComment(
            $launchPost,
            $users['linmei'],
            '作者资料、活动抽屉和新版栅格现在能一起工作，不再只是零散的前台卡片。',
            'approved',
        );

        $this->upsertComment(
            $launchPost,
            $users['sunhao'],
            '如果后面再补一轮图片素材和更多人物数据，这个主题会更接近完整杂志站。',
            'approved',
            $commentTwo->id,
        );

        $this->upsertComment(
            $launchPost,
            $users['wuyue'],
            '这条评论保留待审核状态，用于验证前台隐藏逻辑。',
            'pending',
            $commentOne->id,
        );

        $launchPost->syncCommentStatistics();

        PostStatistic::query()->updateOrCreate(
            ['post_id' => $launchPost->id],
            [
                'views' => 328,
                'likes' => 28,
                'comments_count' => $launchPost->fresh()->statistics?->comments_count ?? 0,
            ],
        );
    }

    private function seedSiteSettings(): void
    {
        $settings = SiteSetting::current();

        $settings->update([
            'site_name' => 'HOMA',
            'site_tagline' => 'Ideas, people and contemporary taste',
            'site_description' => '刊物风格的内容门户，围绕人物、设计、生活方式、科技与活动进行持续更新。',
            'logo_text' => 'HO',
            'seo_title' => 'HOMA',
            'seo_description' => '为 xiaofang 主题准备的刊物风首页、作者页和文章详情页演示数据。',
            'hero_eyebrow' => 'EDITORIAL JOURNAL',
            'hero_title' => 'A magazine-style front page for ideas, authors and contemporary living.',
            'hero_body' => '现在的主题不再只是功能骨架，而是带有明确视觉语气的内容首页，能同时承接文章、作者与活动。',
            'hero_primary_label' => 'Read the latest',
            'hero_primary_url' => '/search',
            'hero_secondary_label' => 'Open events',
            'hero_secondary_url' => '/events',
            'featured_posts_limit' => 10,
            'featured_categories_limit' => 5,
            'featured_tags_limit' => 8,
            'primary_navigation' => [
                ['label' => 'Features', 'url' => '/search'],
                ['label' => 'Journal', 'url' => '/categories/culture'],
                ['label' => 'Authors', 'url' => '/authors/linmei'],
                ['label' => 'Events', 'url' => '/events'],
                ['label' => 'Membership', 'url' => '/pricing'],
            ],
            'footer_navigation' => [
                ['label' => 'Post Layouts', 'url' => '/search'],
                ['label' => 'Authors', 'url' => '/authors/linmei'],
                ['label' => 'Events', 'url' => '/events'],
                ['label' => 'Shop', 'url' => '/shop'],
            ],
            'social_links' => [
                ['label' => 'Facebook', 'url' => 'https://example.com/facebook'],
                ['label' => 'Instagram', 'url' => 'https://example.com/instagram'],
                ['label' => 'LinkedIn', 'url' => 'https://example.com/linkedin'],
            ],
            'business_settings' => array_merge($settings->business_settings ?? [], [
                'home_sections_eyebrow' => 'Sections',
                'home_sections_title' => '栏目导航',
                'home_latest_eyebrow' => 'Latest Stories',
                'home_latest_title' => '最新文章',
                'home_tags_eyebrow' => 'Topics',
                'home_tags_title' => '热门主题',
                'home_flash_eyebrow' => 'Flash',
                'home_flash_title' => '快讯',
                'home_roadmap_eyebrow' => 'Roadmap',
                'home_roadmap_title' => 'Membership, events and commerce',
            ]),
            'footer_copyright' => '© HOMA. Published with the xiaofang theme redesign.',
        ]);
    }

    private function editorialBody(string $summary, string $title): string
    {
        return <<<HTML
<p>{$summary}</p>
<p>{$title} 并不是为了制造一种更高冷的阅读距离，而是希望把内容重新组织成读者更愿意停留的版面：一部分是视觉上的留白与层级，一部分是信息之间的相互照应。</p>
<p>当首页、作者页和文章详情页开始共享同一套叙事逻辑时，用户看到的不再只是孤立的页面，而是一个能自然引导浏览、点击和继续阅读的刊物流。</p>
<p>这也是这轮改造的价值所在：让主题从功能毛坯房进入真正可以拿来展示、联调和继续延展的阶段。</p>
HTML;
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
