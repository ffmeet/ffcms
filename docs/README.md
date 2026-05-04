# FFMeet 1.0 开发文档

这套文档面向三个目标：

1. 支撑 `1.0` 暂存版上线前检查。
2. 帮助后续开发者快速理解架构并进行二次开发。
3. 为生产环境运维、缓存、主题配置和权限边界提供统一说明。

路径约定：

- 文档里出现的 `<PROJECT_ROOT>` 表示当前项目安装目录。
- 例如 `<PROJECT_ROOT>/app/Http/Controllers/Web/HomeController.php`，应替换成你自己服务器或本地机器上的实际项目路径。

版本约定：

- 当前项目基于 `Laravel 12`。
- 当前后台基于 `Filament 5.3` 系列（`composer.json` 约束为 `filament/filament: ^5.3`）。
- 如果后续升级到 Filament 6 或其他大版本，应先重新核对表单 API、页面壳层、导航注册方式和 Livewire 交互写法。

建议阅读顺序：

1. [发布清单](./release/1.0-release-plan.md)
2. [1.0 审计与安全复核](./release/1.0-readiness-audit.md)
3. [对外源码包与安装说明](./release/source-package-and-install.md)
4. [公开仓库前检查清单](./release/public-repository-checklist.md)
5. [Git 初始化与首个提交说明](./release/git-initialization-and-first-commit.md)
6. [GitHub 仓库命名与首次发布说明](./release/github-repository-launch-guide.md)
7. [系统总览](./architecture/overview.md)
8. [主题与首页位置系统](./architecture/themes-and-homepage-slots.md)
9. [会员、订单与支付链路](./architecture/membership-and-commerce.md)
10. [二次开发指南](./development/secondary-development.md)
11. [安装与部署说明](./development/installation-and-deployment.md)
12. [主题模板函数调用规范](./development/theme-template-call-conventions.md)
13. [控制器 / Builder / Blade 三层分工示例](./development/controller-builder-blade-layering.md)
14. [首页内容调用开发说明](./development/homepage-content-calls.md)
15. [文章与活动调用说明](./development/content-and-event-calls.md)
16. [会员与支付调用说明](./development/member-commerce-calls.md)
17. [Filament 插件与版本清单](./development/filament-plugin-inventory.md)
18. [路由与权限矩阵](./development/routes-and-permissions.md)
19. [缓存与部署](./operations/cache-and-deployment.md)
20. [安全基线](./security/security-baseline.md)
21. [支付渠道接入与验签矩阵](./security/payment-provider-matrix.md)

目录导航见：

[SUMMARY.md](./SUMMARY.md)
