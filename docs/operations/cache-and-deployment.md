# 缓存与部署

## 当前缓存层

### 1. 站点设置缓存

- 入口：`SiteSetting::current()`
- 用途：减少频繁读取站点配置

### 2. 前台结果缓存

- 入口：`FrontendCache`
- 用途：缓存首页等前台聚合结果

### 3. 视图缓存

- 入口：`php artisan view:cache`

## 缓存失效

当前已经具备：

- 保存设置后清理站点配置缓存
- 首页设置保存后刷新前台缓存版本
- 后台缓存中心一键清缓存

## 常用命令

```bash
php artisan optimize:clear
php artisan view:cache
npm run build
```

## 部署建议

1. 先备份数据库和 `.env`
2. 部署代码
3. 构建前端资源
4. 清理并重建缓存
5. 登录后台确认主题、首页位、品牌设置
6. 回归首页、会员中心、活动、商品、邮件
