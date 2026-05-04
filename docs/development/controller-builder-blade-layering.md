# 控制器 / Builder / Blade 三层分工示例

这页不讲抽象原则，只讲实际落地时三层各写什么。

目标只有一个：让二次开发时，新增一个列表页、首页区块或专题页，不会把逻辑重新塞回模板。

## 一页结论

- `Controller`：收请求、拿依赖、传视图。
- `Builder / Support`：做页面编排、切片、回退、去重、缓存。
- `Blade`：只负责展示和跳转。

## 示例 1：首页区块

当前 `xiaofang` 首页就是这套结构：

- Controller：
  `<PROJECT_ROOT>/app/Http/Controllers/Web/HomeController.php`
- Builder：
  `<PROJECT_ROOT>/app/Support/Homepage/XiaofangHomepageBuilder.php`
- Blade：
  `<PROJECT_ROOT>/resources/views/themes/xiaofang/pages/home.blade.php`

### Controller 应该写什么

```php
$homepage = $builder->build();

return view(\App\Support\SiteTheme::view('pages.home'), [
    'heroLeadPost' => $homepage['hero_lead_post'],
    'latestList' => $homepage['latest_list'],
]);
```

控制器做的事：

1. 调用 builder。
2. 把最终视图变量传给 Blade。
3. 不在这里写 HTML 结构。

### Builder 应该写什么

```php
return [
    'hero_lead_post' => $heroLeadPost,
    'latest_list' => $latestList,
    'group_items' => $groupItems,
];
```

builder 做的事：

1. 按 slot 配置取数。
2. 处理 fallback。
3. 处理分类位、数量上限、排序。
4. 组织成模板能直接消费的数据。

### Blade 应该写什么

```blade
@if ($heroLeadPost)
    <x-post-card :post="$heroLeadPost" />
@endif

@foreach ($latestList as $post)
    <a href="{{ route('posts.show', $post) }}">{{ $post->title }}</a>
@endforeach
```

模板做的事：

1. 判断是否显示。
2. 输出标题、摘要、分类、作者。
3. 生成链接。

## 示例 2：分类列表页

推荐结构：

- Controller：
  取分类、查文章、分页、确定精选文章。
- Builder：
  通常可以不需要；只有当列表布局很复杂时再引入。
- Blade：
  只渲染精选位、文章网格、分页。

### 推荐写法

```php
return view(\App\Support\SiteTheme::view('pages.category-show'), [
    'category' => $category,
    'featuredPost' => $featuredPost,
    'posts' => $posts,
]);
```

### 不推荐写法

```blade
@php
    $featuredPost = $posts->first();
    $restPosts = $posts->slice(1);
@endphp
```

原因很简单：只要布局一变，这段切片逻辑就会散到多个模板里。

## 示例 3：活动详情页

活动详情页通常不需要 builder，但仍然要坚持“控制器先算好”。

推荐控制器准备：

- `event`
- `relatedEvents`
- `registrationState`
- `paymentState`
- `canRegister`

然后 Blade 只管显示：

```blade
@if ($canRegister)
    <a href="{{ route('events.checkout', $event) }}">立即报名</a>
@endif
```

不要在模板里临时判断：

```blade
@if ($event->starts_at->isFuture() && $event->capacity > $event->registrations_count)
```

这种规则应留在控制器或支持类里，不要让模板理解业务。

## 什么时候必须引入 Builder

下面三种情况，建议直接建 builder，不要犹豫：

1. 一个页面有多个内容区块。
2. 需要 fallback、默认分类、位置位或缓存。
3. 模板里已经开始出现 `first / slice / reject / take / concat`。

## 什么时候不必引入 Builder

下面这些情况，控制器直接传数据就够了：

1. 普通详情页。
2. 结构单一的分页列表页。
3. 没有复杂回退和分组的表单页。

## 二次开发时的判断顺序

每次加新页面，按这个顺序判断：

1. 这是简单页还是编排页？
2. 如果只是单列表 / 单详情，控制器直接传。
3. 如果已经有多个区块，立刻建 builder。
4. Blade 最后只接现成变量。

## 推荐文件落点

- 控制器：
  `<PROJECT_ROOT>/app/Http/Controllers/Web`
- 页面编排类：
  `<PROJECT_ROOT>/app/Support`
  或
  `<PROJECT_ROOT>/app/Support/Homepage`
- 主题模板：
  `<PROJECT_ROOT>/resources/views/themes/<theme>`

## 最短的实战标准

如果你在 Blade 里开始“重新组织数据”，说明这段逻辑已经放错层了。

如果你在 Controller 或 Builder 里能把变量命名成模板一眼就能用的样子，分层通常就是对的。
