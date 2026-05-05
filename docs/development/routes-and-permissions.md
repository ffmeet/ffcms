# 路由与权限矩阵

## 前台公开路由

- `/`
- `/categories/{slug}`
- `/tags/{slug}`
- `/authors/{username}`
- `/posts/{slug}`
- `/events`
- `/events/{slug}`
- `/shop`
- `/shop/{slug}`
- `/pricing`
- `/search`

## 认证路由

- `/login`
- `/register`
- `/forgot-password`
- `/reset-password/{token}`

## 会员路由

统一受 `auth` 与 `member.permission:*` 控制。

核心入口：

- `/member`
- `/member/posts`
- `/member/comments`
- `/member/orders`
- `/member/subscriptions`
- `/member/activities`

## 后台相关入口

- `/admin`
- `/admin/quick-search`
- `/admin/staff-profile`

## 权限判断来源

核心逻辑在：

- [EnsureMemberPermission.php](/Volumes/小芳侠/网站代码库/帝国 cms/ffmeet/laravel-ecms/app/Http/Middleware/EnsureMemberPermission.php:1)
- [User.php](/Volumes/小芳侠/网站代码库/帝国 cms/ffmeet/laravel-ecms/app/Models/User.php:1)
- [MemberGroup.php](/Volumes/小芳侠/网站代码库/帝国 cms/ffmeet/laravel-ecms/app/Models/MemberGroup.php:1)

## 发布建议

每次新增路由时，都检查三件事：

1. 是否应该公开访问
2. 是否需要认证
3. 是否需要成员权限、后台权限或环境限制
