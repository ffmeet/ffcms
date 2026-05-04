# 支付渠道接入与验签矩阵

更新时间：2026-05-02

## 当前状态总览

目前系统已经具备：

- 渠道开关
- 渠道基础凭证字段
- 订单、支付、订阅、活动报名的业务联动
- webhook 入口
- 生产环境默认阻断诊断型 webhook

目前系统尚未具备：

- 真实渠道签名校验
- 渠道级 webhook 幂等策略
- 正式支付成功确认逻辑

因此，`1.0` 版本只适合作为“支付骨架已经搭好，但真实网关仍未正式接入”的观察版。

## 推荐接入顺序

建议只优先接一条正式渠道，不要四条同时推进。

推荐顺序：

1. `Stripe`
2. `支付宝`
3. `微信支付`
4. `PayPal`

原因：

- `Stripe` 的前后端、webhook 和签名校验路径最清晰，适合作为第一条正式化渠道。
- 支付宝和微信支付更贴近中文场景，但接入细节更重。
- `PayPal` 更适合有明确国际支付需求时再接。

## 渠道矩阵

### Stripe

当前已有字段：

- `stripe_enabled`
- `stripe_publishable_key`
- `stripe_secret_key`
- `stripe_webhook_secret`

当前缺口：

- 没有实际创建 Checkout Session / Intent
- webhook 未校验 `Stripe-Signature`
- 未按 `event.type` 做正式支付回写

正式接入最低要求：

1. 前端或服务端创建 Stripe 支付会话
2. webhook 使用 `stripe_webhook_secret` 校验签名
3. 只接受明确的成功事件类型
4. 对同一事件 ID 做幂等处理

推荐结论：

- 最适合优先接入

### 支付宝

当前已有字段：

- `alipay_enabled`
- `alipay_app_id`
- `alipay_pid`
- `alipay_public_key`

当前缺口：

- 缺少商户私钥字段
- 缺少正式下单参数生成逻辑
- 缺少支付宝异步通知验签逻辑

正式接入最低要求：

1. 补商户私钥配置
2. 实现下单签名
3. webhook / notify 回调验签
4. 验证订单金额、商户号、订单号一致性

推荐结论：

- 如果主要面向国内用户，可以作为第二优先级

### 微信支付

当前已有字段：

- `wechat_enabled`
- `wechat_app_id`
- `wechat_mch_id`
- `wechat_api_v3_key`

当前缺口：

- 缺少商户私钥 / 证书序列号
- 缺少平台证书管理逻辑
- 缺少微信回调签名与时间戳校验

正式接入最低要求：

1. 补商户私钥相关字段
2. 实现统一下单或 JSAPI / Native 支付流程
3. 校验回调签名、时间戳、随机串
4. 验证金额与商户订单号一致

推荐结论：

- 商业上很重要，但开发与维护复杂度较高

### PayPal

当前已有字段：

- `paypal_enabled`
- `paypal_client_id`
- `paypal_client_secret`

当前缺口：

- 没有 PayPal order create / capture 流程
- 没有 webhook ID / 签名验证配置
- 没有事件映射逻辑

正式接入最低要求：

1. 补 webhook 校验配置
2. 实现订单创建与捕获流程
3. 对回调事件做签名验证与幂等处理

推荐结论：

- 国际化需要时再接，不建议作为第一条渠道

## 生产环境策略

在正式签名校验没有完成之前，建议保持：

- `payment_mode = sandbox`
- 或者即使切换到 `production`，也只保留人工 / 线下渠道

当前代码中，若 `payment_mode = production`：

- 模拟支付已禁用
- 诊断型 webhook 已阻断

这套策略的目的是：

- 避免“看起来像上线了支付”，实际上还没有真正安全闭环

## 建议下一步

### 方案 A：先接 Stripe

适合快速完成第一条正式支付链路。

建议动作：

1. 接 Stripe Checkout / PaymentIntent
2. 实现 `Stripe-Signature` 校验
3. 把成功回调正式接到 `PaymentLifecycleManager::markPaid`

### 方案 B：先接支付宝

适合优先服务国内用户。

建议动作：

1. 补商户私钥字段
2. 接支付下单
3. 接异步通知验签
4. 完成订单金额与订单号校验

## 结论

如果以“最低风险、最快跑通”作为目标，建议先做 `Stripe`。  
如果以“国内真实用户最常用”作为目标，建议先做 `支付宝`。
