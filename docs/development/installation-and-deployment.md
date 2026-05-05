# 安装与部署说明

这份说明面向接手源码的开发者或部署方，目标是在一页内讲清：

1. 解压源码包后需要做哪些安装步骤。
2. 本地开发与生产部署分别怎么启动。
3. 依赖安装与版本更新应如何处理。

## 1. 交付形态

当前推荐交付物是源码包：

- `ffmeet-v1.0.0-source.tar.gz`

源码包解压后**不能直接运行**，仍需完成标准 Laravel 安装流程。

## 2. 环境要求

- PHP `8.2+`
- Composer `2.x`
- Node.js `20+`
- npm `10+`
- SQLite 或 MySQL / MariaDB

推荐数据库支持口径：

- 开发默认：`SQLite`
- 生产推荐：`MySQL 8+ / MariaDB 10.6+`

## 3. 本地开发安装步骤

```bash
tar -xzf ffmeet-v1.0.0-source.tar.gz
cd ffmeet-v1.0.0-source
cp .env.example .env
composer install
npm install
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm run build
php artisan ffmeet:create-admin admin admin@example.com --name="Site Admin"
composer serve:upload-safe
```

默认开发入口：

- 前台：`http://127.0.0.1:8000`
- 后台：`http://127.0.0.1:8000/admin/login`

## 4. 生产部署步骤

```bash
tar -xzf ffmeet-v1.0.0-source.tar.gz
cd ffmeet-v1.0.0-source
cp .env.example .env
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan view:cache
php artisan ffmeet:create-admin admin admin@example.com --name="Site Admin"
```

生产环境 `.env` 至少需要确认：

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL` 为正式域名
- `DB_CONNECTION`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `MAIL_*`

生产环境建议运行方式：

- `Nginx/Apache + PHP-FPM`
- 队列、缓存、日志策略按服务器环境单独配置

不建议长期使用 `php artisan serve` 作为生产运行方式。

## 5. 开始运行的命令是什么

### 本地开发

推荐：

```bash
composer serve:upload-safe
```

这是项目内置的本地开发启动方式，适合带上传测试的场景。

普通 Laravel 调试也可以使用：

```bash
php artisan serve
```

### 生产环境

生产环境不是通过单个 artisan 命令常驻运行，而是通过 Web Server 提供服务：

- `Nginx/Apache`
- `PHP-FPM`

## 6. 默认管理员账号说明

默认安装只执行 `migrate` 时，不会自动生成固定后台账号。

首个管理员应显式创建：

```bash
php artisan ffmeet:create-admin admin admin@example.com --name="Site Admin"
```

如果命令里不传 `--password`，系统会安全提示输入密码。

## 7. 依赖会不会自动更新

默认**不会自动升级到最新版依赖**。

安装过程中的行为是：

- `composer install` 按 `composer.lock` 安装锁定版本
- `npm install` 按 `package-lock.json` 安装锁定版本

这意味着：

- 同一源码包在不同机器上的依赖版本应保持一致
- 不会因为安装时联网而自动升到未知新版本

## 8. 如果确实需要升级依赖

应由开发者手动执行，并重新验证：

```bash
composer update
npm update
```

升级依赖后，建议至少重新执行：

```bash
npm run build
php artisan optimize:clear
php artisan view:cache
php artisan test
```

## 9. 推荐的交付说明口径

对外说明时，建议统一使用以下口径：

- 当前交付物是源码部署包
- 默认不附带固定后台账号
- 安装后需要手动创建首个管理员
- 安装过程默认使用锁定依赖版本，不自动升级
- 真实支付渠道仍需后续正式接入
