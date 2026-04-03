<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Models\Category;
use App\Models\ContentModel;
use App\Models\Post;
use App\Models\Tag;
use App\Support\ContentModelFieldManager;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\ToolbarButtonGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Slimani\MediaManager\Form\MediaPicker;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make([
                    'default' => 1,
                    'xl' => 12,
                ])
                    ->schema([
                        Grid::make(1)
                            ->columnSpan([
                                'xl' => 8,
                            ])
                            ->schema([
                                Section::make(fn (Get $get): string => static::isFlashModel($get) ? '快讯内容' : '写作区')
                                    ->schema([
                                        Hidden::make('post_kind')
                                            ->default(static::resolveRequestKind())
                                            ->dehydrated(false),
                                        TextInput::make('title')
                                            ->label(fn (Get $get): string => static::isFlashModel($get) ? '快讯标题' : '标题')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (?string $state, Set $set, ?string $old): void {
                                                if (blank($state) || blank($old)) {
                                                    $set('slug', Str::slug((string) $state));

                                                    return;
                                                }

                                                if (Str::slug($old) === Str::slug((string) $state)) {
                                                    $set('slug', Str::slug((string) $state));
                                                }
                                            })
                                            ->maxLength(255),
                                        Textarea::make('summary')
                                            ->label(fn (Get $get): string => static::isFlashModel($get) ? '快讯摘要' : '摘要')
                                            ->required(fn (Get $get): bool => static::isFlashModel($get))
                                            ->rows(fn (Get $get): int => static::isFlashModel($get) ? 6 : 4)
                                            ->placeholder(fn (Get $get): string => static::isFlashModel($get)
                                                ? '直接写出快讯核心信息，前台会优先展示这段摘要。'
                                                : '可选填写文章摘要，不填时系统会尝试从正文自动提取。'),
                                        static::makeContentEditor()
                                            ->visible(fn (Get $get): bool => ! static::isFlashModel($get)),
                                        Section::make('补充正文')
                                            ->icon('heroicon-o-plus-circle')
                                            ->visible(fn (Get $get): bool => static::isFlashModel($get))
                                            ->collapsible()
                                            ->collapsed()
                                            ->compact()
                                            ->schema([
                                                static::makeContentEditor()
                                                    ->hiddenLabel(),
                                            ])
                                            ->columnSpanFull(),
                                        Section::make('模型字段')
                                            ->description('根据当前内容模型自动显示扩展字段。')
                                            ->visible(fn (Get $get): bool => ContentModelFieldManager::buildPostFormFieldsForModelId($get('model_id')) !== [])
                                            ->schema(fn (Get $get): array => ContentModelFieldManager::buildPostFormFieldsForModelId($get('model_id')))
                                            ->columnSpanFull(),
                                    ]),
                                Section::make(fn (Get $get): string => static::isFlashModel($get) ? '录入信息' : '基础信息')
                                    ->columns(2)
                                    ->schema([
                                        Select::make('user_id')
                                            ->relationship('user', 'username')
                                            ->label('作者')
                                            ->searchable()
                                            ->preload()
                                            ->default(fn (): ?int => auth()->id())
                                            ->helperText('默认当前发布者，可下拉改为其他作者。')
                                            ->required(),
                                        TextInput::make('author_name')
                                            ->label('署名作者')
                                            ->placeholder('不填写时默认显示前台登录用户')
                                            ->maxLength(120),
                                        Hidden::make('model_id')
                                            ->default(fn (Get $get): ?int => static::defaultModelId($get)),
                                    ]),
                                Actions::make([
                                    Action::make('cancelCreate')
                                        ->label('取消发布')
                                        ->color('gray')
                                        ->outlined()
                                        ->url(\App\Filament\Resources\Posts\PostResource::getUrl('index')),
                                    Action::make('createAnother')
                                        ->label('发布另一个')
                                        ->color('gray')
                                        ->outlined()
                                        ->action('createAnother'),
                                    Action::make('create')
                                        ->label('发布')
                                        ->color('primary')
                                        ->action('create'),
                                ])
                                    ->visible(fn (): bool => request()->routeIs('filament.admin.resources.posts.create'))
                                    ->alignment(Alignment::End)
                                    ->extraAttributes(['class' => 'ecms-inline-create-actions']),
                            ]),
                        Grid::make(1)
                            ->columnSpan([
                                'xl' => 4,
                            ])
                            ->schema([
                                Section::make('标签')
                                    ->visible(fn (Get $get): bool => ! static::isFlashModel($get))
                                    ->schema([
                                        TagsInput::make('tag_names')
                                            ->label('标签')
                                            ->hiddenLabel()
                                            ->placeholder('输入标签后按回车')
                                            ->suggestions(fn (): array => Tag::query()->orderBy('name')->pluck('name')->all())
                                            ->splitKeys(['Tab', ',']),
                                    ]),
                                Section::make(fn (Get $get): string => static::isFlashModel($get) ? '快讯发布' : '发布设置')
                                    ->inlineLabel()
                                    ->schema([
                                        Select::make('status')
                                            ->required()
                                            ->label('发布状态')
                                            ->options([
                                                'draft' => '草稿',
                                                'pending' => '待审核',
                                                'published' => '已发布',
                                            ])
                                            ->default('published')
                                            ->native(false),
                                        Select::make('category_id')
                                            ->label('栏目')
                                            ->options(fn (Get $get): array => static::categoryOptions($get))
                                            ->searchable()
                                            ->preload()
                                            ->default(fn (Get $get): ?int => static::defaultCategoryId($get))
                                            ->live()
                                            ->afterStateUpdated(function (mixed $state, Set $set): void {
                                                $modelId = filled($state)
                                                    ? Category::query()->whereKey($state)->value('model_id')
                                                    : null;

                                                $set('model_id', $modelId);
                                            })
                                            ->required(),
                                        DateTimePicker::make('published_at')
                                            ->label(fn (Get $get): string => static::isFlashModel($get) ? '时间' : '发布时间')
                                            ->default(now())
                                            ->helperText(null),
                                        TextInput::make('slug')
                                            ->label('别名')
                                            ->required()
                                            ->hidden(fn (Get $get): bool => static::isFlashModel($get))
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255),
                                        Actions::make([
                                            Action::make('publishFromSidebar')
                                                ->label('发布')
                                                ->icon('heroicon-o-paper-airplane')
                                                ->color('primary')
                                                ->action('publishFromSidebar'),
                                        ])
                                            ->alignment(Alignment::End)
                                            ->fullWidth(),
                                    ]),
                                Section::make('封面')
                                    ->schema([
                                        MediaPicker::make('coverMediaFiles')
                                            ->relationship('coverMediaFiles')
                                            ->collection('cover')
                                            ->directory('posts/covers')
                                            ->label('封面媒体'),
                                    ]),
                                Group::make([
                                    TextInput::make('seo_title')
                                        ->label('SEO 标题')
                                        ->visible(fn (Get $get): bool => ! static::isFlashModel($get))
                                        ->maxLength(255),
                                    Placeholder::make('model_hint')
                                        ->hiddenLabel()
                                        ->content(fn (Get $get): HtmlString => new HtmlString(sprintf(
                                            '<div class="ecms-model-chip"><span class="ecms-model-chip-label">内容模型</span><span class="ecms-model-chip-value">%s</span></div>',
                                            static::isFlashModel($get) ? '快讯模型' : '新闻模型',
                                        ))),
                                ])
                                    ->extraAttributes(['class' => 'ecms-side-meta-block']),
                            ]),
                    ]),
            ])
            ->columns(1);
    }

    protected static function makeContentEditor(): RichEditor
    {
        return RichEditor::make('content')
            ->label('正文')
            ->required(fn (Get $get): bool => ! static::isFlashModel($get))
            ->toolbarButtons([
                'blockquote',
                'bold',
                'bulletList',
                'codeBlock',
                'h2',
                'h3',
                'highlight',
                'italic',
                'link',
                'orderedList',
                ToolbarButtonGroup::make('对齐方式', [
                    'alignStart',
                    'alignCenter',
                    'alignEnd',
                    'alignJustify',
                ])
                    ->icon('fi-o-align-start'),
                'textColor',
                'horizontalRule',
                'redo',
                'strike',
                'underline',
                'undo',
            ])
            ->disableToolbarButtons(['attachFiles'])
            ->textColors([
                'slate' => '#334155',
                'blue' => '#2563eb',
                'emerald' => '#059669',
                'amber' => '#d97706',
                'rose' => '#e11d48',
            ])
            ->customTextColors()
            ->extraInputAttributes(['class' => 'ecms-post-editor'])
            ->columnSpanFull();
    }

    protected static function isFlashModel(Get $get): bool
    {
        return static::resolvePostKind($get) === 'flash' || Post::isFlashModelId($get('model_id'));
    }

    protected static function resolvePostKind(Get $get): string
    {
        $kind = (string) ($get('post_kind') ?: '');

        if (in_array($kind, ['article', 'flash'], true)) {
            return $kind;
        }

        return Post::isFlashModelId($get('model_id')) ? 'flash' : 'article';
    }

    protected static function resolveRequestKind(): string
    {
        $kind = request()->route('kind');

        return in_array($kind, ['article', 'flash'], true) ? $kind : 'article';
    }

    protected static function defaultModelId(Get $get): ?int
    {
        $kind = static::resolvePostKind($get);

        return ContentModel::query()
            ->where('table_name', $kind === 'flash' ? 'posts_flash' : 'posts_news')
            ->value('id');
    }

    protected static function defaultCategoryId(Get $get): ?int
    {
        return Category::query()
            ->where('model_id', static::defaultModelId($get))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->value('id');
    }

    protected static function categoryOptions(Get $get): array
    {
        return Category::query()
            ->where('model_id', static::defaultModelId($get) ?: $get('model_id'))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('name', 'id')
            ->all();
    }
}
