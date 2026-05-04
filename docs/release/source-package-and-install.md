# 对外源码包与安装说明

这份说明只回答两件事：

1. 如何把项目安全地交给外部开发者或客户。
2. 如何保证本地密码、日志、数据库或调试文件不会被一并打包。

## 1. 推荐对外发布形态

当前项目最稳的发布方式，不是图形安装器，而是**源码部署包**。

建议只对外提供这一类包：

- `ffmeet-v1.0.0-source.tar.gz`

它适合：

- 外部开发者二次开发
- 服务器手工部署
- 测试环境安装

不建议直接压缩当前机器上的完整项目目录再对外分发。

## 2. 为什么不能直接压缩本地目录

因为本地项目目录里通常会混入这些内容：

- `.env`
- 当前机器数据库，如 `database/database.sqlite`
- `storage/logs/*`
- `storage/debugbar/*`
- 编译缓存
- 当前机器上传文件
- 当前机器绝对路径相关缓存

这些文件里可能包含：

- 数据库密码
- 邮件账号密码
- 支付密钥
- 调试记录
- 测试用户数据

## 3. 当前项目已经提供的安全打包方式

使用：

```bash
<PROJECT_ROOT>/scripts/build-source-package.sh v1.0.0
```

脚本会自动排除：

- `.env`
- `database/database.sqlite`
- `storage/logs`
- `storage/debugbar`
- `vendor`
- `node_modules`
- `public/build`
- `public/storage`
- `.git`

忽略规则在：

`<PROJECT_ROOT>/.releaseignore`

## 4. 对外发布前必须检查

### 必查 1：不要带 `.env`

真正发出的包里，必须只有：

- `.env.example`

绝不能包含：

- `.env`
- `.env.production`
- `.env.backup`

### 必查 2：不要带本地数据库

真正发出的包里，必须不包含：

- `database/database.sqlite`

### 必查 3：不要带日志和调试记录

真正发出的包里，必须不包含：

- `storage/logs/*.log`
- `storage/debugbar/*.json`

### 必查 4：不要带本地上传文件

真正发出的包里，必须不包含：

- `public/storage/*`

## 5. 外部安装方式

### 方式 A：SQLite（开发 / 演示）

1. 解压源码包。
2. 复制环境文件：

```bash
cp .env.example .env
```

3. 安装依赖：

```bash
composer install
npm install
npm run build
```

4. 生成密钥：

```bash
php artisan key:generate
```

5. 创建 SQLite 文件并迁移：

```bash
touch database/database.sqlite
php artisan migrate
```

6. 创建首个管理员：

```bash
php artisan ffmeet:create-admin admin admin@example.com --name="Site Admin"
```

### 方式 B：MySQL / MariaDB（生产推荐）

1. 解压源码包。
2. 复制环境文件：

```bash
cp .env.example .env
```

3. 在 `.env` 中填写：

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://your-domain.com`
- `DB_CONNECTION=mysql`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `MAIL_*`

4. 安装依赖并构建：

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

5. 生成密钥并迁移：

```bash
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan view:cache
```

6. 创建首个管理员：

```bash
php artisan ffmeet:create-admin admin admin@example.com --name="Site Admin"
```

## 6. 建议附带给外部开发者的文件

除了源码包，建议一起提供：

- `docs/README.md`
- `docs/SUMMARY.md`
- `docs/release/1.0-release-plan.md`
- `docs/release/1.0-readiness-audit.md`

这样接手方能快速知道：

- 系统版本定位
- 当前未完成项
- 数据库与部署要求
- 安全边界

## 7. 建议对外说明口径

当前版本建议这样表述：

- 开发默认数据库：`SQLite`
- 正式推荐生产数据库：`MySQL 8+ / MariaDB 10.6+`
- 当前交付形态：源码部署包
- 当前未正式打通真实支付网关
- 默认安装不附带固定后台账号

## 8. 默认账号说明

默认安装只执行 `migrate` 时，不会自动创建后台账号。

当前推荐方式是：

```bash
php artisan ffmeet:create-admin admin admin@example.com --name="Site Admin"
```

只有在显式执行 `db:seed`，并且环境为 `local / testing`，或手动开启：

```bash
FFMEET_ALLOW_DEMO_SEED=true
```

系统才会写入演示账号和演示内容。

## 9. 最短的安全规则

如果一个文件只属于当前机器环境、当前机器数据或当前机器密钥，它就不应该进入对外交付包。
