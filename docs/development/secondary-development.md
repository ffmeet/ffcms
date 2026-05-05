# 二次开发指南

## 适合从哪里入手

### 改前台页面

优先看：

- `resources/views/themes/xiaofang`
- `app/Http/Controllers/Web`
- `app/Support/SiteTheme.php`

### 改后台设置

优先看：

- `app/Filament/Pages`
- `app/Filament/Resources/SiteSettings/Schemas/SiteSettingForm.php`
- `app/Support/SettingsNavigation.php`

### 改会员、订单、支付逻辑

优先看：

- `app/Http/Controllers/Web/CommerceActionController.php`
- `app/Http/Controllers/Web/MemberOrderController.php`
- `app/Support/PaymentLifecycleManager.php`

## 开发约定

1. 主题逻辑尽量集中在 `SiteTheme` 和对应主题目录，不要把多个主题判断散落到 Blade。
2. 首页配置优先通过“位置位”扩展，不要再引入依赖文案命名的新字段。
3. 新的后台设置项，优先挂到现有设置中心体系，不要各自新增孤立入口。
4. 对生产有副作用的调试能力，必须显式加环境限制。

## 新功能推荐流程

1. 先确认放在前台、会员中心还是后台。
2. 再确认是否应进入 `SiteSetting`。
3. 再确认是否需要加入缓存失效链路。
4. 最后补回归测试或最少补文档。
