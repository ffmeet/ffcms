# GitHub 仓库命名与首次发布说明

这份说明面向准备把 `FFMeet` 对外发布到 GitHub 的维护者，目标是统一仓库命名、项目描述、标签和首次发布口径。

## 推荐仓库名

优先建议：

- `ffmeet`

可选备选：

- `ffmeet-cms`
- `ffmeet-platform`
- `ffmeet-community-preview`

建议说明：

- 如果后续计划长期把它作为主项目维护，优先使用 `ffmeet`。
- 如果希望在仓库名里直接体现“内容管理 / 站点平台”属性，可以使用 `ffmeet-cms`。
- 如果当前阶段只想公开一个观察版，且后面可能拆分产品线，可以使用 `ffmeet-community-preview`。

## 推荐仓库简介

建议使用以下简短描述：

> FFMeet is a Laravel + Filament publishing and membership platform for editorial sites, events, and commerce.

中文内部说明可理解为：

> 一个基于 Laravel 与 Filament 的内容发布、会员、活动与轻电商平台。

## 推荐 Topics

建议在 GitHub 仓库 Topics 中加入：

- `laravel`
- `filament`
- `cms`
- `publishing`
- `membership`
- `events`
- `commerce`
- `php`
- `sqlite`
- `mysql`

如果未来单独强调主题系统，也可以补充：

- `theme-system`
- `editorial`

## 推荐可见性策略

建议按以下顺序发布：

1. 先建私有仓库，导入整理后的 `v1.0.0` 基线。
2. 在私有状态下完成一次公开前检查。
3. 确认 `README`、`LICENSE`、`docs`、源码包流程都正常后，再切成公开仓库。

不建议直接把当前工作目录未经整理地推送到公开仓库。

## 推荐仓库首页文案

可以直接放在仓库首页摘要或首次发布说明里：

> FFMeet is a production-observation release of an editorial publishing platform with memberships, events, commerce scaffolding, themeable frontend rendering, and a public developer documentation center.

## 首次发布建议标题

建议第一个 GitHub Release 使用：

`v1.0.0 - Production Observation Release`

## 首次发布建议正文

可直接使用以下版本：

```md
## FFMeet v1.0.0

FFMeet v1.0.0 is the first public production-observation release.

### Included in this release

- Editorial publishing workflows
- Theme-based frontend rendering
- Membership center
- Orders, subscriptions, and event registration scaffolding
- Public developer documentation center
- Homepage slot configuration system
- Secure source-package workflow for external delivery

### Environment baseline

- Laravel 12
- Filament 5.3
- SQLite for local development
- MySQL / MariaDB recommended for production

### Important notes

- Real third-party payment gateways are not fully closed in this release and should be enabled carefully per provider.
- Default installation does not create a fixed administrator account.
- The first administrator should be created with:

  `php artisan ffmeet:create-admin <username> <email> --name="<display name>"`

### Suggested first steps

1. Read the installation guide.
2. Create the first administrator account.
3. Review the public developer docs.
4. Verify environment configuration before production use.
```

## 推荐置顶 Issue

如果准备开放社区反馈，建议创建一个置顶 Issue：

标题：

`Welcome to FFMeet v1.0 feedback`

正文建议：

```md
Welcome to the FFMeet public repository.

If you are evaluating or extending FFMeet, please use this thread for:

- installation feedback
- deployment issues
- documentation gaps
- architecture questions
- theme-system extension suggestions

For reproducible bugs, prefer opening a separate bug report with environment details and clear reproduction steps.
```

## 发布前最后核对

在真正公开仓库之前，建议至少再次确认以下项目：

- `README.md` 已更新为对外口径
- `LICENSE` 已确认符合预期授权范围
- `docs/` 可直接在线浏览
- `.env`、数据库、日志、上传文件不会进入仓库或源码包
- `composer package:source` 可成功生成安全源码包
- `CHANGELOG.md` 已包含当前版本说明

如需完整核对项，请继续参考：

- [公开仓库前检查清单](./public-repository-checklist.md)
