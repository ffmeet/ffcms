# 首页内容调用开发说明

> 适用主题：当前重点针对 `xiaofang`
>
> 用途：帮助开发者快速理解首页里的“头条、精选、推荐、分类位、排序和数量”到底是怎么调用的，以及后台该怎么配置。

## 1. 首页内容调用分两层

当前首页的数据来源不是只靠一种方式，而是两层共同决定：

1. 文章内容本身的编辑位标记
2. 首页位置位（slot）配置

也就是说：

- “头条 / 精选 / 推荐”更多是文章自己的属性
- “第一屏左栏 / 第二屏左栏 / 创作灵感区”更多是首页结构位置

这两层会一起参与首页取数。

## 2. 头条怎么调用

头条文章来自 `posts` 表里的：

- `is_headline = true`

对应作用域在：

- `<PROJECT_ROOT>/app/Models/Post.php`

```php
public function scopeHeadline(Builder $query): Builder
{
    return $query->where('is_headline', true);
}
```

首页控制器会优先查一篇最新头条：

- `<PROJECT_ROOT>/app/Http/Controllers/Web/HomeController.php`

调用方式大致是：

```php
$leadPost = (clone $contentPostsQuery)
    ->headline()
    ->latest('published_at')
    ->first();
```

如果没有任何文章被标成头条：

- 系统会回退到已发布文章池里的第一篇

## 3. 精选列表怎么调用

精选文章来自：

- `is_featured = true`

对应作用域：

```php
public function scopeFeaturedPlacement(Builder $query): Builder
{
    return $query->where('is_featured', true);
}
```

首页控制器会优先拉取精选候选：

```php
$featuredCandidates = (clone $contentPostsQuery)
    ->featuredPlacement()
    ->when($leadPost, fn ($query) => $query->whereKeyNot($leadPost->getKey()))
    ->latest('published_at')
    ->limit(max(2, $featuredPostsLimit))
    ->get();
```

通常可理解为：

- 优先拿到被勾成“精选”的文章
- 自动排除当前头条
- 按发布时间倒序补足

## 4. 推荐怎么调用

推荐文章来自：

- `is_recommended = true`

对应作用域：

```php
public function scopeRecommendedPlacement(Builder $query): Builder
{
    return $query->where('is_recommended', true);
}
```

首页里推荐通常不是单独整页输出，而是作为精选池补充候选：

```php
$recommendedCandidates = (clone $contentPostsQuery)
    ->recommendedPlacement()
    ->when($leadPost, fn ($query) => $query->whereKeyNot($leadPost->getKey()))
    ->whereNotIn('id', $featuredCandidates->pluck('id')->all())
    ->latest('published_at')
    ->limit(2)
    ->get();
```

也就是说：

- 推荐文章会参与首页精选补位
- 但不会和已选中的精选文章重复

## 5. 头条、精选、推荐之间的互斥关系

在当前系统里，这三个标记不是完全自由叠加的。

发布数据规范化时会自动做互斥处理：

- 如果文章是头条：
  - 会自动取消精选和推荐
- 如果文章是精选：
  - 会自动取消推荐
- 如果文章是推荐：
  - 会自动取消精选

相关逻辑在：

- `<PROJECT_ROOT>/app/Models/Post.php`

```php
if ($data['is_headline']) {
    $data['is_featured'] = false;
    $data['is_recommended'] = false;
}

if ($data['is_featured']) {
    $data['is_recommended'] = false;
}

if ($data['is_recommended']) {
    $data['is_featured'] = false;
}
```

这意味着首页不会出现“同一篇文章同时既是头条又是精选”的混乱状态。

## 6. 如何按照分类调用

首页大部分列表位，实际是按分类位来取数的。

当前主题 `xiaofang` 的结构位置位包括：

- `slot_01` 第一屏左栏：头条 + 下方简讯
- `slot_02` 第一屏中栏：两条精选
- `slot_03` 第一屏右栏：最新文章列表
- `slot_04` 第二屏左栏：文化 / 设计卡片组
- `slot_05` 第二屏右栏：订阅入口 + 活动列表
- `slot_06` 第三屏：创作灵感区
- `slot_07` 第四屏：继续阅读区

分类读取入口在：

- `<PROJECT_ROOT>/app/Support/SiteTheme.php`

```php
SiteTheme::homepageSlotCategoryIds($theme, 'slot_04', $businessSettings)
```

控制器会按这些分类 ID 取文章，而不是写死某个栏目名称。

## 7. 分类位支持什么配置

每个可配置位置位，当前支持三类参数：

### 7.1 `category_ids`

分类来源数组。

示例：

```php
business_settings.theme_homepage.xiaofang.slot_04.category_ids
```

特点：

- 可以选多个分类
- 同一个分类可以被多个位置复用
- 空值会被自动过滤
- 最终会转成唯一整数数组

### 7.2 `limit`

该位置最多取几条内容。

示例：

```php
business_settings.theme_homepage.xiaofang.slot_03.limit
```

处理规则：

- 大于 `0` 时按配置生效
- 最大值会被压到 `20`
- 如果没填，则回退到该位置默认值

例如：

- `slot_03` 默认是 `7`
- `slot_06` 默认是 `4`

### 7.3 `sort`

排序方式。

当前允许值：

- `latest`
- `oldest`
- `featured_first`
- `recommended_first`

读取入口：

```php
SiteTheme::homepageSlotSort($theme, 'slot_03', $businessSettings)
```

如果值不合法，会自动回退成：

- `latest`

## 8. 后台具体怎么设置

后台“首页设置”页里，每个位置位都对应一组配置：

- 分类来源
- 取数上限
- 排序方式

对开发者来说，推荐理解方式是：

1. 先确认当前页面区块对应哪个 `slot`
2. 再看该 `slot` 是否允许配置分类
3. 最后决定是：
   - 纯分类驱动
   - 精选 / 推荐优先
   - 或者走默认 fallback

## 9. 哪些位置不走文章分类调用

当前 `xiaofang` 里：

- `slot_05`

是系统位，不是普通文章分类位。

它当前承载的是：

- 订阅入口
- 活动列表

因此这个位置不按 `category_ids` 拉文章，而是单独走订阅与活动逻辑。

## 10. 首页控制器的真实取数思路

首页控制器入口：

- `<PROJECT_ROOT>/app/Http/Controllers/Web/HomeController.php`

整体思路可以概括成：

1. 先构建已发布内容查询池
2. 先尝试拿头条、精选、推荐
3. 再按 slot 配置读取分类位
4. 如果某个 slot 没配，就回退到默认分类或默认池
5. 尽量避免区块之间重复取同一篇文章

这意味着首页不是“单一规则硬编码”，而是：

- 编辑位
- 分类位
- 数量
- 排序
- fallback

几层一起配合。

## 11. 默认 fallback 是什么

如果后台某个 slot 没有配置分类，系统不会直接空掉。

会按内置策略回退，例如：

- 第一屏左栏优先新闻中心
- 第二屏左栏优先文化
- 设计区块优先设计
- 灵感区和继续阅读会从预设分类里补内容

所以：

- 后台没配，不代表首页没数据
- 但配了以后，应优先以后台 slot 配置为准

## 12. 开发时最常看的文件

如果要继续改首页调用，优先看这几个文件：

1. `<PROJECT_ROOT>/app/Http/Controllers/Web/HomeController.php`
2. `<PROJECT_ROOT>/app/Support/SiteTheme.php`
3. `<PROJECT_ROOT>/app/Models/Post.php`
4. `<PROJECT_ROOT>/resources/views/themes/xiaofang/pages/home.blade.php`
5. `<PROJECT_ROOT>/app/Filament/Pages/HomepageCenter.php`

## 13. 推荐给开发者的理解顺序

建议按这个顺序理解首页：

1. 先看 `Post` 上的编辑位：
   - 头条
   - 精选
   - 推荐
2. 再看 `SiteTheme` 的 slot 蓝图：
   - 哪个位置对应哪个区块
3. 再看 `HomeController`：
   - 各 slot 怎么取数
   - fallback 怎么补
4. 最后再看前端模板：
   - 数据最终怎么展示

## 14. 一句话总结

当前首页调用规则可以概括成：

- 头条：看 `is_headline`
- 精选：看 `is_featured`
- 推荐：看 `is_recommended`
- 分类列表：看 `slot_xx.category_ids`
- 数量：看 `slot_xx.limit`
- 排序：看 `slot_xx.sort`

如果后台没配，就回退到控制器里的默认首页策略。
