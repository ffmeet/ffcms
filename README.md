# FFMeet

FFMeet 是一套基于 `Laravel 12` 与 `Filament 5.3` 的内容、会员、活动、商品与订单一体化系统。

当前 `v1.0` 的定位是**生产观察版**：

- 前台主题浏览与内容发布链路已基本闭环
- 会员中心、评论、作者、活动、商品、订单与订阅链路已可用
- 后台提供主题切换、首页设置、缓存中心与公开开发文档中心
- 真实第三方支付仍建议后续按单一渠道继续正式接入

## 当前版本能力

- 多主题前台结构，当前主用主题为 `xiaofang`
- 文章、分类、标签、作者页与评论系统
- 活动列表、活动详情、报名与订单闭环
- 商品详情、会员订阅、订单与支付基础链路
- 公开开发文档中心
- 后台首页位置位配置与缓存管理

## 技术基线

- PHP `^8.2`
- Laravel `^12.0`
- Filament `^5.3`
- Livewire `4.x`
- 默认开发数据库：`SQLite`
- 正式推荐生产数据库：`MySQL 8+ / MariaDB 10.6+`

## 快速开始

### 本地开发

```bash
cp .env.example .env
composer install
npm install
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm run build
composer serve:upload-safe
```

如需后台管理员，请手动创建：

```bash
php artisan ffmeet:create-admin admin admin@example.com --name="Site Admin"
```

### 生产部署

```bash
cp .env.example .env
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan view:cache
```

然后创建首个管理员：

```bash
php artisan ffmeet:create-admin admin admin@example.com --name="Site Admin"
```

生产环境请至少确认：

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL` 为正式域名
- `DB_*` 为正式数据库配置
- `MAIL_*` 为真实邮件配置

## 安全对外发布源码

不要直接压缩你本机项目目录发给别人。

当前项目已经提供安全源码包脚本：

```bash
composer package:source
```

或：

```bash
bash scripts/build-source-package.sh v1.0.0
```

它会自动排除：

- `.env`
- 本地 SQLite 数据库
- `storage/logs`
- `storage/debugbar`
- `vendor`
- `node_modules`
- `public/storage`
- `.git`

详细说明见：

- [对外源码包与安装说明](./docs/release/source-package-and-install.md)

默认安装不会自动附带固定后台用户名和密码。

## 开发文档

项目内已经整理了公开开发文档，重点包括：

- 发布与审计
- 主题与首页位置系统
- 文章、活动、会员与支付调用说明
- 主题模板函数调用规范
- Filament 插件与版本清单
- 路由与权限矩阵

入口见：

- [文档首页](./docs/README.md)
- [文档目录](./docs/SUMMARY.md)

## 常用脚本

### 上传安全启动

```bash
composer serve:upload-safe
```

这个脚本会以更高上传限制启动本地 PHP Server，适合测试：

- 前台封面上传
- Filament 媒体上传
- 媒体库附件创建

### 安全源码打包

```bash
composer package:source
```

## 当前边界

`v1.0` 目前最重要的未完成项是：

- 真实第三方支付渠道的正式落地与验签闭环

因此，当前更适合：

- 先上线生产观察版
- 用真实流量验证前台、会员、内容、活动、商品与订单链路
- 后续再按单一优先渠道补正式支付

## 许可证

当前仓库附带：

- [LICENSE](./LICENSE)

当前许可证采用保守的社区预览口径，适合公开代码、评估、测试与二次开发准备阶段；如需更宽的商用或再分发权限，应另行明确授权方案。
