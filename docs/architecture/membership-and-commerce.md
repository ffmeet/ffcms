# 会员、订单与支付链路

## 会员体系

会员权限由：

- `users.group_id`
- `member_groups.permissions`

共同决定。

前台多数会员能力都通过 `EnsureMemberPermission` 中间件控制。

## 会员中心

主要控制器：

- [MemberDashboardController.php](/Volumes/小芳侠/网站代码库/帝国 cms/ffmeet/laravel-ecms/app/Http/Controllers/Web/MemberDashboardController.php:1)
- [MemberPostController.php](/Volumes/小芳侠/网站代码库/帝国 cms/ffmeet/laravel-ecms/app/Http/Controllers/Web/MemberPostController.php:1)
- [MemberCommentController.php](/Volumes/小芳侠/网站代码库/帝国 cms/ffmeet/laravel-ecms/app/Http/Controllers/Web/MemberCommentController.php:1)
- [MemberOrderController.php](/Volumes/小芳侠/网站代码库/帝国 cms/ffmeet/laravel-ecms/app/Http/Controllers/Web/MemberOrderController.php:1)
- [MemberSubscriptionController.php](/Volumes/小芳侠/网站代码库/帝国 cms/ffmeet/laravel-ecms/app/Http/Controllers/Web/MemberSubscriptionController.php:1)
- [MemberActivityController.php](/Volumes/小芳侠/网站代码库/帝国 cms/ffmeet/laravel-ecms/app/Http/Controllers/Web/MemberActivityController.php:1)

## 订单创建入口

### 商品购买

- 控制器：[CommerceActionController.php](/Volumes/小芳侠/网站代码库/帝国 cms/ffmeet/laravel-ecms/app/Http/Controllers/Web/CommerceActionController.php:1)
- 行为：创建订单，必要时创建待支付记录

### 会员订阅

- 同一控制器
- 会创建订单 + 订阅记录

### 活动报名

- 同一控制器
- 根据活动是否收费，决定是否创建待支付记录

## 支付生命周期

统一由 [PaymentLifecycleManager.php](/Volumes/小芳侠/网站代码库/帝国 cms/ffmeet/laravel-ecms/app/Support/PaymentLifecycleManager.php:1) 驱动：

- `markPaid`
- `markFailed`
- `markClosed`

支付成功后会联动：

- 订单状态
- 订阅激活
- 活动报名状态
- 商品库存扣减

## 当前现实状态

支付闭环在“系统内业务联动”层面已经打通。  
但真实支付渠道还没有完成正式安全校验与联调。
