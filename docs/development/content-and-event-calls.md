# 文章与活动列表/详情调用说明

> 适用范围：文章系统、分类列表、标签列表、活动列表、活动详情  
> 目标：一页讲清入口函数、查询逻辑、计算逻辑和模板调用方式。

## 1. 路由入口

- 文章详情：`/posts/{slug}`
- 分类列表：`/categories/{slug}`
- 标签列表：`/tags/{slug}`
- 活动列表：`/events`
- 活动详情：`/events/{slug}`

对应入口文件：

- `<PROJECT_ROOT>/routes/web.php`

## 2. 文章详情怎么调用

入口控制器：

- `<PROJECT_ROOT>/app/Http/Controllers/Web/PostController.php`

调用方式：

```php
Route::get('/posts/{slug}', PostController::class)->name('posts.show');
```

模板里常用写法：

```php
route('posts.show', $post->slug)
```

### 查询逻辑

详情页用 `slug` 查一篇已发布文章，并预加载展示需要的关联：

```php
$post = Post::query()
    ->with([
        'category',
        'contentModel',
        'user',
        'detail',
        'tags',
        'statistics',
        'coverMediaFiles.media',
        'attachmentMediaFiles.media',
    ])
    ->where('slug', $slug)
    ->published()
    ->firstOrFail();
```

### 计算逻辑

文章详情页当前还有三段附加计算：

1. 附件归并  
`loadResolvedAttachments()` 会把媒体库附件和会员上传附件统一装载；  
`normalizeAttachments()` 再把两套来源整理成统一数组。

2. 评论树构建  
`buildCommentThreads()` 会按 `parent_id` 把评论整理成树，并补出：
- `depth_level`
- `reply_count`
- `has_active_path`

3. 相关阅读  
同分类取 4 篇：

```php
->where('category_id', $post->category_id)
->whereKeyNot($post->id)
->published()
->latest('published_at')
->limit(4)
```

4. 作者更多内容  
同作者再取 3 篇已发布文章。

## 3. 分类列表怎么调用

入口控制器：

- `<PROJECT_ROOT>/app/Http/Controllers/Web/CategoryController.php`

调用方式：

```php
Route::get('/categories/{slug}', CategoryController::class)->name('categories.show');
route('categories.show', $category->slug)
```

### 查询逻辑

先按 `slug` 找栏目，再取该栏目下的文章：

```php
$posts = Post::query()
    ->with(['contentModel', 'user', 'statistics', 'detail', 'coverMediaFiles.media'])
    ->where('category_id', $category->id)
    ->published()
    ->latest('published_at')
    ->paginate(12);
```

### 附加数据

- `relatedCategories`：其它栏目，按 `sort_order` 取 6 个
- `trendingTags`：热门标签，按 `count desc, name asc` 取 12 个

## 4. 标签列表怎么调用

入口控制器：

- `<PROJECT_ROOT>/app/Http/Controllers/Web/TagController.php`

调用方式：

```php
Route::get('/tags/{slug}', TagController::class)->name('tags.show');
route('tags.show', $tag->slug)
```

### 查询逻辑

标签列表走标签关系查询，而不是手写 `whereIn`：

```php
$posts = $tag->posts()
    ->with(['category', 'contentModel', 'user', 'statistics', 'detail', 'coverMediaFiles.media'])
    ->published()
    ->latest('published_at')
    ->paginate(12)
    ->withQueryString();
```

### 附加数据

- `trendingTags`：热门标签
- `featuredCategories`：推荐栏目

## 5. 活动列表怎么调用

入口控制器：

- `<PROJECT_ROOT>/app/Http/Controllers/Web/EventController.php`

调用方式：

```php
Route::get('/events', [EventController::class, 'index'])->name('events.index');
route('events.index')
```

### 查询逻辑

活动列表只展示三种状态：

- `registration-open`
- `sold-out`
- `finished`

主列表：

```php
$events = Event::query()
    ->with('memberGroup')
    ->whereIn('status', ['registration-open', 'sold-out', 'finished'])
    ->orderBy('starts_at')
    ->paginate(12)
    ->withQueryString();
```

高亮列表：

```php
$highlightedEvents = Event::query()
    ->with('memberGroup')
    ->whereIn('status', ['registration-open', 'sold-out'])
    ->orderBy('starts_at')
    ->limit(3)
    ->get();
```

### 统计逻辑

列表页会额外给模板一个 `eventMetrics`：

- `all_events`：三种公开状态的总数
- `open_events`：开放报名中的数量
- `paid_events`：公开状态里 `is_paid = true` 的数量

## 6. 活动详情怎么调用

入口控制器：

- `<PROJECT_ROOT>/app/Http/Controllers/Web/EventController.php`

调用方式：

```php
Route::get('/events/{slug}', [EventController::class, 'show'])->name('events.show');
route('events.show', $event->slug)
```

### 查询逻辑

活动详情页按 `slug` 找一条公开活动：

```php
$event = Event::query()
    ->with(['memberGroup'])
    ->where('slug', $slug)
    ->whereIn('status', ['registration-open', 'sold-out', 'finished'])
    ->firstOrFail();
```

相关推荐：

```php
$relatedEvents = Event::query()
    ->with('memberGroup')
    ->whereIn('status', ['registration-open', 'sold-out', 'finished'])
    ->whereKeyNot($event->id)
    ->orderBy('starts_at')
    ->limit(4)
    ->get();
```

### 计算逻辑

活动模型里有两段常用状态函数：

文件：

- `<PROJECT_ROOT>/app/Models/Event.php`

1. `hasReachedCapacity()`  
判断活动是否达到人数上限；会统计 `pending` 和 `approved` 两种报名状态。

2. `isRegistrationAvailable()`  
判断是否还能报名，条件是：
- `status` 必须是 `registration-open`
- `registration_closes_at` 不能过期
- 不能达到人数上限

## 7. 文章系统常用作用域与函数

文件：

- `<PROJECT_ROOT>/app/Models/Post.php`

最常用的查询能力有：

- `published()`：只取 `status = published` 且 `published_at` 不为空
- `nonFlash()`：排除快讯模型
- `headline()`：头条
- `featuredPlacement()`：精选
- `recommendedPlacement()`：推荐

这些作用域主要给：

- 首页调用
- 栏目列表
- 标签列表
- 文章详情相关推荐

## 8. 模板里推荐怎么写

列表页和详情页模板里，推荐统一只做“路由调用”，不要在模板里重写查询逻辑。

常见写法：

```php
route('posts.show', $post->slug)
route('categories.show', $category->slug)
route('tags.show', $tag->slug)
route('events.show', $event->slug)
route('events.index')
```

不推荐在 Blade 里再做：

- 大量 `filter() / reject() / slice()`
- 重新拼列表
- 二次统计

这些逻辑应优先放在：

- Controller
- 或单独的 Builder / Presenter / ViewModel

## 9. 开发时优先看哪几个文件

- 路由入口：`<PROJECT_ROOT>/routes/web.php`
- 文章详情：`<PROJECT_ROOT>/app/Http/Controllers/Web/PostController.php`
- 分类列表：`<PROJECT_ROOT>/app/Http/Controllers/Web/CategoryController.php`
- 标签列表：`<PROJECT_ROOT>/app/Http/Controllers/Web/TagController.php`
- 活动列表/详情：`<PROJECT_ROOT>/app/Http/Controllers/Web/EventController.php`
- 文章模型能力：`<PROJECT_ROOT>/app/Models/Post.php`
- 活动模型能力：`<PROJECT_ROOT>/app/Models/Event.php`

如果你要二次开发新的列表页/详情页，建议顺序是：

1. 先看路由入口
2. 再看 Controller 当前传了哪些 view data
3. 最后再看 Blade 里如何展示

这样最快，也最不容易把业务逻辑重新写进模板。
