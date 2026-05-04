# 会员中心、订单、订阅与支付调用说明

> 适用范围：会员中心首页、订单列表、支付页、订阅列表、前台商品购买、订阅开通、活动报名  
> 目标：一页讲清入口路由、控制器函数、核心计算逻辑、模板推荐调用方式。

## 1. 路由入口

核心前台入口都定义在：

- `<PROJECT_ROOT>/routes/web.php`

常用路由：

- `/member` 会员中心首页
- `/member/orders` 我的订单
- `/member/orders/{order}/pay` 订单支付页
- `/member/subscriptions` 我的订阅
- `/shop/{slug}/purchase` 商品购买
- `/pricing/{slug}/subscribe` 套餐订阅
- `/events/{slug}/register` 活动报名

## 2. 会员中心首页怎么调用

入口控制器：

- `<PROJECT_ROOT>/app/Http/Controllers/Web/MemberDashboardController.php`

调用方式：

```php
Route::get('/member', MemberDashboardController::class)->name('member.dashboard');
route('member.dashboard')
```

### 查询逻辑

控制器会先取当前登录用户，再补两类最近记录：

```php
$recentPosts = Post::query()
    ->with('category')
    ->where('user_id', $user->id)
    ->latest()
    ->limit(8)
    ->get();
```

```php
Comment::query()
    ->with('post')
    ->where('user_id', $user->id)
    ->latest()
    ->limit(5)
    ->get();
```

### 计算逻辑

首页汇总数据不在 Blade 里算，而是交给：

- `<PROJECT_ROOT>/app/Support/MemberOperationsSummary.php`

当前主要调用：

- `dashboardMetrics($user)`：会员中心顶部计数
- `attentionCards($user)`：右侧或中部提醒卡片

## 3. 订单列表怎么调用

入口控制器：

- `<PROJECT_ROOT>/app/Http/Controllers/Web/MemberOrderController.php`

调用方式：

```php
Route::get('/member/orders', [MemberOrderController::class, 'index'])->name('member.orders.index');
route('member.orders.index')
```

### 查询逻辑

订单列表永远从“当前用户自己的订单关系”取：

```php
$user->orders()
    ->with(['payments', 'purchasable'])
    ->latest()
    ->paginate(12)
    ->withQueryString()
```

### 计算逻辑

列表页摘要卡片同样走：

- `MemberOperationsSummary::orderSummary($user)`

所以模板里不需要自己去算：

- 待支付数量
- 已支付数量
- 已关闭数量

## 4. 支付页怎么调用

入口控制器：

- `<PROJECT_ROOT>/app/Http/Controllers/Web/MemberOrderController.php`

调用方式：

```php
Route::get('/member/orders/{order}/pay', [MemberOrderController::class, 'pay'])->name('member.orders.pay');
route('member.orders.pay', $order)
```

### 查询与校验逻辑

支付页首先会做三层判断：

1. 订单必须属于当前用户
2. 金额必须大于 `0`
3. 订单状态不能已经是 `paid`

然后再装载：

```php
$order->load(['payments', 'purchasable']);
```

最后取最新一条支付记录：

```php
$payment = $order->payments->sortByDesc('id')->first();
```

如果没有支付记录，会直接回订单页并提示。

### 支付渠道怎么给模板

支付页模板拿到的渠道列表来自：

- `<PROJECT_ROOT>/app/Support/PaymentProviderRegistry.php`

控制器调用：

```php
PaymentProviderRegistry::checkoutProviders(
    SiteSetting::current()->business_settings ?? []
)
```

这意味着支付页显示哪些渠道，不是在模板里写死，而是由后台支付设置决定。

## 5. 我的订阅怎么调用

入口控制器：

- `<PROJECT_ROOT>/app/Http/Controllers/Web/MemberSubscriptionController.php`

调用方式：

```php
Route::get('/member/subscriptions', [MemberSubscriptionController::class, 'index'])->name('member.subscriptions.index');
route('member.subscriptions.index')
```

### 查询逻辑

订阅列表从当前用户关系取：

```php
$user->subscriptions()
    ->with(['plan', 'lastOrder'])
    ->latest()
    ->paginate(12)
    ->withQueryString()
```

### 计算逻辑

订阅页摘要卡片：

- `MemberOperationsSummary::subscriptionSummary($user)`

提醒卡片：

- `MemberOperationsSummary::attentionCards($user)`

## 6. 商品购买怎么调用

入口控制器：

- `<PROJECT_ROOT>/app/Http/Controllers/Web/CommerceActionController.php`

调用方式：

```php
Route::post('/shop/{slug}/purchase', [CommerceActionController::class, 'purchaseProduct'])->name('shop.purchase');
route('shop.purchase', $product->slug)
```

### 查询逻辑

只允许购买已发布商品：

```php
Product::query()
    ->where('slug', $slug)
    ->where('status', 'published')
    ->firstOrFail();
```

### 计算逻辑

商品购买会先判断：

- 是否缺货
- 是否免费

然后在事务里创建：

1. `orders`
2. `payments`（如果金额大于 0）

免费商品：

- 订单直接写成 `paid`
- `paid_at` 立即写入

付费商品：

- 订单写成 `pending`
- 跳转到 `member.orders.pay`

## 7. 套餐订阅怎么调用

调用方式：

```php
Route::post('/pricing/{slug}/subscribe', [CommerceActionController::class, 'subscribe'])->name('pricing.subscribe');
route('pricing.subscribe', $plan->slug)
```

### 查询逻辑

只允许订阅：

- `slug` 命中的套餐
- `is_active = true`

同时会先检查当前用户是否已经存在：

- `pending`
- `active`

这两种状态的同套餐订阅

避免重复开通。

### 计算逻辑

事务里会一起创建：

1. 订单 `orders`
2. 支付记录 `payments`
3. 订阅记录 `user_subscriptions`

免费套餐：

- 订单直接 `paid`
- 订阅直接 `active`
- `started_at / expires_at` 立即写入

付费套餐：

- 订单 `pending`
- 订阅 `pending`
- 跳到支付页继续付款

## 8. 活动报名怎么调用

调用方式：

```php
Route::post('/events/{slug}/register', [CommerceActionController::class, 'registerEvent'])->name('events.register');
route('events.register', $event->slug)
```

### 查询逻辑

先按 `slug` 找活动，然后做一整套前置校验：

- 活动状态必须是 `registration-open`
- 用户必须有 `events.access`
- 如果活动限制会员组，用户必须有权限进入该会员组
- 报名截止时间不能过期
- 名额不能已满
- 用户不能已有 `pending / approved` 报名记录

### 计算逻辑

活动分两种：

1. 免费活动
2. 付费活动

免费活动：

- 直接创建 `event_registrations`
- 状态写成 `approved`
- `payment_status = not_required`

付费活动：

- 先创建订单 `orders`
- 再创建待支付记录 `payments`
- 再创建活动报名 `event_registrations`
- 报名状态先记成 `pending`
- 最后跳转到订单支付页

## 9. 待支付记录怎么生成

创建待支付记录的统一入口在：

- `CommerceActionController::createPendingPayment()`

核心规则：

```php
if ((float) $order->amount <= 0) {
    return null;
}
```

也就是说：

- 金额小于等于 `0` 的订单不会生成待支付记录
- 付费订单才会生成 `payments`

默认支付渠道来自：

```php
PaymentProviderRegistry::defaultProvider(
    SiteSetting::current()->business_settings ?? []
)
```

## 10. 模板里推荐怎么调用

模板层推荐只做路由输出，不自己重新写业务判断。

常见写法：

```php
route('member.dashboard')
route('member.orders.index')
route('member.orders.pay', $order)
route('member.subscriptions.index')
route('shop.purchase', $product->slug)
route('pricing.subscribe', $plan->slug)
route('events.register', $event->slug)
```

不推荐在 Blade 里自己判断：

- 是否免费
- 是否要跳支付
- 是否需要生成支付记录
- 是否可以报名

这些都应该在控制器或业务函数里做掉。

## 11. 开发时优先看哪几个文件

- 会员中心首页：`<PROJECT_ROOT>/app/Http/Controllers/Web/MemberDashboardController.php`
- 订单与支付页：`<PROJECT_ROOT>/app/Http/Controllers/Web/MemberOrderController.php`
- 订阅列表：`<PROJECT_ROOT>/app/Http/Controllers/Web/MemberSubscriptionController.php`
- 商品购买 / 套餐订阅 / 活动报名：`<PROJECT_ROOT>/app/Http/Controllers/Web/CommerceActionController.php`
- 支付渠道注册：`<PROJECT_ROOT>/app/Support/PaymentProviderRegistry.php`
- 汇总卡片计算：`<PROJECT_ROOT>/app/Support/MemberOperationsSummary.php`

推荐的阅读顺序：

1. 路由入口
2. 对应 Web Controller
3. 业务支持类
4. 最后再看主题模板

这样最容易判断：  
哪些逻辑已经在函数层做完，哪些地方模板只需要展示。 
