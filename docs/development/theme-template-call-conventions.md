# 主题模板函数调用规范

这页只讲一个目标：`Blade` 模板层应该如何调用数据，哪些逻辑应该留在控制器、构建器或支持类里。

## 一句话原则

模板负责展示，函数层负责取数和编排。

也就是说：

- 模板里可以：输出变量、跑 `route()`、引用 partial、做轻量空值判断。
- 模板里不要：直接查模型、自己 `filter / reject / slice / concat` 大量编排数据、写主题分支逻辑。

## 1. 主题视图怎么调用

优先通过 `<PROJECT_ROOT>/app/Support/SiteTheme.php` 里的 `SiteTheme::view()` 解析主题视图，不要在控制器里写死 `themes.xiaofang...`。

推荐：

```php
return view(\App\Support\SiteTheme::view('pages.home'), $data);
```

Blade 里继承布局也同理：

```blade
@extends(\App\Support\SiteTheme::view('layout'))
```

这样切主题时，系统会优先找当前主题文件，找不到再走回退视图。

## 2. partial 和组件怎么调用

固定且明确的主题 partial，可以直接引用主题路径：

```blade
@include('themes.xiaofang.partials.header')
```

如果这个 partial 未来可能被其他主题复用，优先用 `SiteTheme::view()`：

```blade
@include(\App\Support\SiteTheme::view(
    'partials.comment-thread',
    'themes.xiaofang.partials.comment-thread'
))
```

## 3. 列表页和详情页应该怎么传数据

推荐做法是控制器先把页面需要的数据整理好，再一次性传给模板。

推荐：

```php
return view(\App\Support\SiteTheme::view('pages.category-show'), [
    'category' => $category,
    'posts' => $posts,
    'featuredPost' => $featuredPost,
]);
```

不推荐在 Blade 里再做：

```blade
@php
    $featuredPost = $posts->first();
    $restPosts = $posts->slice(1);
@endphp
```

这类“取第一条、切剩余列表、重新分组”的工作，应该留在控制器或构建器层。

## 4. 首页这种复杂页面怎么处理

首页不要在模板里拼业务结构，应该交给专门构建器。

当前 `xiaofang` 首页已经走：

- `<PROJECT_ROOT>/app/Http/Controllers/Web/HomeController.php`
- `<PROJECT_ROOT>/app/Support/Homepage/XiaofangHomepageBuilder.php`

模板 `<PROJECT_ROOT>/resources/views/themes/xiaofang/pages/home.blade.php` 只消费这些现成变量：

- `heroLeadPost`
- `stripStory`
- `editorialStories`
- `latestList`
- `groupLead`
- `groupItems`
- `designGroupLead`
- `designGroupItems`
- `inspirationStories`
- `readMoreStories`

后续新主题首页，也建议照这个模式做，不要把首页变成第二个控制器。

## 5. 模板里允许调用哪些常见函数

建议保留在模板里的调用，主要就这几类：

### 路由

```blade
route('posts.show', $post)
route('categories.show', $category)
route('tags.show', $tag)
route('authors.show', $author)
route('events.show', $event)
route('shop.show', $product)
```

### 轻量展示判断

```blade
@if ($post->category)
@if ($posts->isNotEmpty())
{{ $post->title }}
```

### 主题视图解析

```blade
\App\Support\SiteTheme::view('layout')
```

## 6. 模板里不建议直接做什么

不建议：

```blade
@php
    $items = \App\Models\Post::published()->featured()->latest()->take(6)->get();
@endphp
```

不建议：

```blade
@php
    $left = $posts->filter(...)->take(1);
    $right = $posts->reject(...)->slice(1, 4);
@endphp
```

不建议：

```blade
@if (\App\Support\SiteTheme::current() === 'xiaofang')
```

这三类问题分别会带来：

- 模板直接碰数据库
- 编排逻辑分散到视图
- 主题判断散落一地，后续难维护

## 7. 分类、标签、作者字段怎么输出

如果页面里展示了分类、标签、作者这些前台可访问对象，优先让字段本身可点击。

示例：

```blade
@if ($post->category)
    <a href="{{ route('categories.show', $post->category) }}">
        {{ $post->category->name }}
    </a>
@endif
```

这类链接属于展示层逻辑，放在 Blade 里是合理的。

## 8. 推荐的分层方式

一页内容建议按下面三层处理：

1. Model / Scope
   负责基础能力，如 `published()`、`featured()`、`headline()`。
2. Controller / Builder / Support
   负责页面编排，如首页区块、列表切片、fallback、缓存。
3. Blade
   负责最终渲染。

## 9. 开发时优先看哪里

- 主题切换与视图解析：
  `<PROJECT_ROOT>/app/Support/SiteTheme.php`
- 首页编排：
  `<PROJECT_ROOT>/app/Support/Homepage/XiaofangHomepageBuilder.php`
- 前台控制器：
  `<PROJECT_ROOT>/app/Http/Controllers/Web`
- 当前主题模板：
  `<PROJECT_ROOT>/resources/views/themes/xiaofang`

## 10. 最简短的判断标准

如果一段代码回答的是“页面该显示什么内容、显示几条、怎么分组”，它就不应该留在 Blade。

如果一段代码回答的是“这一条内容怎么渲染、点哪里、显示什么文案”，它通常可以留在 Blade。
