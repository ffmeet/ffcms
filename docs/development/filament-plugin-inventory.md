# Filament 插件与版本清单

本文档用于快速说明当前后台基于哪一代 Filament，以及额外接入了哪些 Filament 相关插件，方便后续开发者在二次开发时快速判断兼容边界。

## 核心版本

- Laravel：`12.x`
- Filament 主包：`filament/filament v5.4.3`
- Livewire：`livewire/livewire v4.2.3`

说明：

- `composer.json` 当前约束为 `filament/filament: ^5.3`
- `composer.lock` 当前实际安装版本为 `v5.4.3`
- 因此本文档默认以 `Filament 5.4.x / Livewire 4.x` 的 API 行为为准

## 当前已明确接入的后台插件

### 1. 媒体管理

- Composer 包：`slimani/filament-media-manager v0.9.11`
- 相关扩展包：
  - `filament/spatie-laravel-media-library-plugin v5.4.3`
  - `hugomyb/filament-media-action v5.0.0.0`
- 面板注册位置：`<PROJECT_ROOT>/app/Providers/Filament/AdminPanelProvider.php`
- 当前用途：
  - 后台媒体库入口
  - 图片与媒体文件的浏览、处理与关联

当前代码里通过以下方式挂入后台：

```php
MediaManagerPlugin::make()
    ->mediaManagerPage(\App\Filament\Media\Pages\MediaManager::class)
```

### 2. 评论系统

- Composer 包：`businesstilto/commentable v0.3.7`
- 面板注册位置：`<PROJECT_ROOT>/app/Providers/Filament/AdminPanelProvider.php`
- 当前用途：
  - 评论能力接入
  - 前后台评论线程与管理能力的基础支撑

当前代码里通过以下方式挂入后台：

```php
CommentablePlugin::make()
```

## 当前项目里存在但不是“主业务插件”的配套包

这些包会影响后台开发体验或字段能力，但当前不是独立业务模块入口：

- `codewithdennis/filament-select-tree v4.0.18`
  - 常用于树形栏目、层级选择场景
- `filament/actions v5.4.3`
- `filament/forms v5.4.3`
- `filament/infolists v5.4.3`
- `filament/notifications v5.4.3`
- `filament/query-builder v5.4.3`
- `filament/schemas v5.4.3`
- `filament/support v5.4.3`
- `filament/tables v5.4.3`
- `filament/widgets v5.4.3`

## 后台插件入口在哪里看

如果后续想继续核对后台到底注册了哪些插件，优先看：

1. `<PROJECT_ROOT>/composer.json`
2. `<PROJECT_ROOT>/composer.lock`
3. `<PROJECT_ROOT>/app/Providers/Filament/AdminPanelProvider.php`

其中：

- `composer.json` 看声明约束
- `composer.lock` 看真实安装版本
- `AdminPanelProvider` 看这些插件有没有真正挂进当前后台面板

## 二次开发提醒

1. 不要只看 `composer.json` 判断版本能力，实际行为应以 `composer.lock` 为准。
2. 如果升级 Filament 大版本，优先回归：
   - 后台导航注册
   - 表单 Schema
   - Livewire 交互
   - 插件兼容性
3. 如果新增后台插件，建议同步更新这份清单，避免后续接手者只能翻代码排查。
