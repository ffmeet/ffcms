# Codex 账号切换交接说明

> 用途：当切换 Codex 账号、重新开新线程、或中断一段时间后，帮助下一位继续工作的 Codex / 人类开发者快速接手当前项目。

## 1. 项目位置

- 工作目录：`/Volumes/小芳侠/网站代码库/帝国 cms/ffmeet/laravel-ecms`
- 项目类型：Laravel 12 + Filament 5
- 当前仓库状态：本地存在大量未提交改动，属于持续开发中的工作树

## 2. 先看哪里

恢复工作时，优先按这个顺序看：

1. `docs/CODEX_EXECUTION_PLAN.md`
2. 本文档 `docs/CODEX_HANDOFF.md`
3. `docs/HOMEPAGE_DATA_SOURCE.md`（如果当前任务涉及首页栏目位、真实分类调用或首页运营配置）
4. `git status --short`
5. 相关测试文件与对应控制器 / 视图

## 3. 当前已形成的主线能力

以下能力已经不再是纯骨架，而是进入“可联调、可回归”的状态：

- CMS 基础数据结构、模型、Filament 后台资源
- 前台认证、会员中心、内容阅读链路
- 评论线程化展示与会员评论管理
- 会员投稿、编辑、送审、栏目绑定模型
- 前台封面上传与后台媒体库打通
- 附件 `attachments` 与媒体库 `media_files` 关联
- 站点设置、主题切换、主题预览
- `default` 与 `xiaofang` 两套前台主题
- 商品 / 订单 / 支付 / 订阅 / 活动 的业务底座
- 模拟支付成功 / 失败 / 关闭的商业化回写链路
- favicon、头像、上传诊断等配套能力

## 4. 当前工作树特征

本地改动范围很大，属于“多个模块一起推进，但围绕同一重构主线”的状态，不是脏改动垃圾堆。主要涉及：

- `app/Filament/*`
- `app/Http/Controllers/Web/*`
- `app/Models/*`
- `app/Support/*`
- `database/migrations/*`
- `database/seeders/*`
- `resources/views/themes/*`
- `resources/views/filament/*`
- `tests/Feature/*`
- `tests/Unit/*`

同时，旧的 `resources/views/site/*` 目录正在被主题化视图替换，当前存在一批删除记录，这是预期内改动。

## 5. 当前最重要的计划基线

以 `docs/CODEX_EXECUTION_PLAN.md` 为准，尤其关注两个区块：

- `## 6. 下一步`
- `## 7. 2026-04 新阶段补充` 下的 `### 当前下一步`

当前可直接延续的工作重点是：

- 继续清理后台低频配置页和内容录入页的默认 CRUD 交互感
- 继续补前后台联调验证，优先围绕媒体库、会员投稿、商业化和第二套主题
- 继续把 `xiaofang` 主题下仍混用默认主题外壳的会员页统一掉

## 6. 上次中断前的明确观察

上次继续推进时，已经确认到一个非常具体的下一步切口：

- `xiaofang` 主题的会员页并没有完全统一
- 已较完整接入 `xiaofang` 外壳的页面：
  - `resources/views/themes/xiaofang/member/dashboard.blade.php`
  - `resources/views/themes/xiaofang/member/posts-create.blade.php`
  - `resources/views/themes/xiaofang/member/posts-edit.blade.php`
- 仍明显混用默认主题外壳 / partial 的页面：
  - `resources/views/themes/xiaofang/member/posts-index.blade.php`
  - `resources/views/themes/xiaofang/member/comments-index.blade.php`
  - `resources/views/themes/xiaofang/member/orders-index.blade.php`
  - `resources/views/themes/xiaofang/member/orders-pay.blade.php`
  - `resources/views/themes/xiaofang/member/subscriptions-index.blade.php`
  - `resources/views/themes/xiaofang/member/activities-center.blade.php`
  - `resources/views/themes/xiaofang/member/activities-index.blade.php`
  - `resources/views/themes/xiaofang/member/profile-edit.blade.php`

也就是说，下一轮最顺手的工作不是再加新系统，而是：

1. 统一这些页面的 layout
2. 统一它们引用的 `topbar / nav / page-header`
3. 补对应的前台视图测试，锁住第二套主题会员中心的一致性

## 7. 测试现状

项目里已经有比较成体系的测试，重点文件包括：

- `tests/Feature/MediaBrowserTest.php`
- `tests/Feature/MemberPostManagementTest.php`
- `tests/Feature/MemberPostSubmissionTest.php`
- `tests/Feature/MemberCommentManagementTest.php`
- `tests/Feature/MemberProfileAvatarTest.php`
- `tests/Feature/FrontendContentTest.php`
- `tests/Feature/FrontendCommerceViewTest.php`
- `tests/Feature/CommerceFlowTest.php`
- `tests/Feature/SiteFaviconGenerationTest.php`
- `tests/Feature/SiteThemeTest.php`
- `tests/Feature/UploadDiagnosticsCenterTest.php`

如果是切账号后第一次恢复，建议优先阅读：

1. `tests/Feature/FrontendCommerceViewTest.php`
2. `tests/Feature/MediaBrowserTest.php`
3. `tests/Feature/MemberPostManagementTest.php`
4. `tests/Feature/MemberProfileAvatarTest.php`

这几组最能快速恢复对“当前主线功能已经走到哪里”的认知。

## 8. 推荐恢复步骤

切换账号后，新的 Codex 建议按下面顺序恢复：

1. 进入项目根目录
2. 先读 `docs/CODEX_EXECUTION_PLAN.md`
3. 再读本文档
4. 执行 `git status --short`
5. 从“上次中断前的明确观察”继续，而不是重新发散选题
6. 做完一轮后，更新执行计划文档中的“已完成 / 进行中 / 下一步”

## 9. 常用命令

```bash
git status --short
php artisan test
php artisan migrate:fresh --seed
composer serve:upload-safe
```

上传相关联调时，优先使用：

```bash
composer serve:upload-safe
```

不要默认用普通 `php artisan serve` 去排查上传问题。

## 10. 风险提醒

- 当前工作树不是干净状态，切换账号前后都不要误用会回滚文件的命令。
- 未提交改动很多，继续开发前应先读改动上下文，不要凭旧记忆覆盖。
- `storage/media-library/temp/*` 下有临时文件，通常不是优先处理对象。
- 如果要提交 git，先明确“交接文档”和“功能改动”是否要拆开提交。

## 11. 给下一位 Codex 的一句话

不要从头分析这个项目。先把它视为“已经完成一大半的 CMS + 会员 + 商业化底座”，然后沿着计划文档里写明的下一步，优先补视觉一致性和联调回归测试。
