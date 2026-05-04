<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\MemberGroup;
use App\Models\MembershipPlan;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Models\UserSubscription;
use App\Support\OrderNumber;
use Illuminate\Database\Seeder;

class CommerceDemoSeeder extends Seeder
{
    public function run(): void
    {
        $defaultGroup = MemberGroup::query()->updateOrCreate(
            ['name' => '默认会员'],
            [
                'min_points' => 0,
                'max_points' => 9999,
                'permissions' => [
                    'site.access' => true,
                    'member.center' => true,
                    'shop.access' => true,
                    'events.access' => true,
                ],
            ],
        );

        $premiumGroup = MemberGroup::query()->updateOrCreate(
            ['name' => '进阶会员'],
            [
                'min_points' => 10000,
                'max_points' => 999999,
                'permissions' => [
                    'site.access' => true,
                    'member.center' => true,
                    'shop.access' => true,
                    'events.access' => true,
                    'events.priority' => true,
                    'shop.discount' => true,
                ],
            ],
        );

        $users = [
            'linmei' => User::query()->firstOrCreate(
                ['username' => 'linmei'],
                [
                    'email' => 'linmei@example.com',
                    'password_hash' => '123456',
                    'group_id' => $premiumGroup->id,
                    'status' => 'active',
                ],
            ),
            'sunhao' => User::query()->firstOrCreate(
                ['username' => 'sunhao'],
                [
                    'email' => 'sunhao@example.com',
                    'password_hash' => '123456',
                    'group_id' => $defaultGroup->id,
                    'status' => 'active',
                ],
            ),
            'chenyu' => User::query()->firstOrCreate(
                ['username' => 'chenyu'],
                [
                    'email' => 'chenyu@example.com',
                    'password_hash' => '123456',
                    'group_id' => $defaultGroup->id,
                    'status' => 'active',
                ],
            ),
        ];

        $users['linmei']->update(['group_id' => $premiumGroup->id]);

        $plans = [
            'free-explorer' => MembershipPlan::query()->updateOrCreate(
                ['slug' => 'free-explorer'],
                [
                    'name' => 'Explorer 免费会员',
                    'description' => '适合先体验内容、活动和会员中心的免费层。',
                    'price' => 0,
                    'billing_period' => 'monthly',
                    'duration_days' => 30,
                    'is_active' => true,
                    'sort_order' => 1,
                    'features' => [
                        '公开内容访问' => '可浏览公开文章与活动信息',
                        '会员中心' => '可查看订单、活动、订阅记录',
                    ],
                ],
            ),
            'growth-monthly' => MembershipPlan::query()->updateOrCreate(
                ['slug' => 'growth-monthly'],
                [
                    'name' => 'Growth 月度会员',
                    'description' => '适合需要持续阅读、活动优先和商店权益的订阅用户。',
                    'price' => 49,
                    'billing_period' => 'monthly',
                    'duration_days' => 30,
                    'is_active' => true,
                    'sort_order' => 2,
                    'features' => [
                        '优先活动报名' => '开放活动优先确认资格',
                        '会员内容访问' => '逐步承接订阅内容权限',
                        '商店权益' => '后续叠加下载与折扣权益',
                    ],
                ],
            ),
            'pro-annual' => MembershipPlan::query()->updateOrCreate(
                ['slug' => 'pro-annual'],
                [
                    'name' => 'Pro 年度会员',
                    'description' => '年度计划，用来模拟长期订阅、自动续费和多付费层展示。',
                    'price' => 399,
                    'billing_period' => 'yearly',
                    'duration_days' => 365,
                    'is_active' => true,
                    'sort_order' => 3,
                    'features' => [
                        '全年访问' => '覆盖全年订阅周期',
                        '活动与商店优先权益' => '为后续商业化流程预留完整承接',
                    ],
                ],
            ),
        ];

        $products = [
            'future-lab-playbook' => Product::query()->updateOrCreate(
                ['slug' => 'future-lab-playbook'],
                [
                    'title' => '未来内容实验手册',
                    'status' => 'published',
                    'cover_image_url' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80',
                    'delivery_type' => 'download',
                    'currency' => 'CNY',
                    'price' => 39,
                    'compare_at_price' => 59,
                    'stock' => 128,
                    'summary' => '一份用于内容、专题、会员和活动联动设计的数字资料包。',
                    'content' => '适合演示下载类数字商品的完整购买路径。',
                    'payload' => ['demo' => true, 'kind' => 'ebook'],
                    'published_at' => now()->subDays(12),
                ],
            ),
            'techsir-notebook-kit' => Product::query()->updateOrCreate(
                ['slug' => 'techsir-notebook-kit'],
                [
                    'title' => '年度科技先生主题笔记套装',
                    'status' => 'published',
                    'cover_image_url' => 'https://images.unsplash.com/photo-1517842645767-c639042777db?auto=format&fit=crop&w=1200&q=80',
                    'delivery_type' => 'physical',
                    'currency' => 'CNY',
                    'price' => 129,
                    'compare_at_price' => 169,
                    'stock' => 24,
                    'summary' => '用来模拟实体商品售卖与库存管理，不把商店锁死成纯数字商品。',
                    'content' => '首版商城仍然走统一商品底座。',
                    'payload' => ['demo' => true, 'kind' => 'physical'],
                    'published_at' => now()->subDays(8),
                ],
            ),
            'vip-salon-pass' => Product::query()->updateOrCreate(
                ['slug' => 'vip-salon-pass'],
                [
                    'title' => '闭门沙龙通行权益',
                    'status' => 'published',
                    'cover_image_url' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1200&q=80',
                    'delivery_type' => 'event-access',
                    'currency' => 'CNY',
                    'price' => 199,
                    'compare_at_price' => 299,
                    'stock' => 18,
                    'summary' => '用来模拟商品购买后承接活动资格的中性权益商品。',
                    'content' => '后续可与活动或会员权益联动。',
                    'payload' => ['demo' => true, 'kind' => 'event-access'],
                    'published_at' => now()->subDays(4),
                ],
            ),
            'starter-member-onboarding' => Product::query()->updateOrCreate(
                ['slug' => 'starter-member-onboarding'],
                [
                    'title' => '新会员欢迎包',
                    'status' => 'published',
                    'cover_image_url' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1200&q=80',
                    'delivery_type' => 'membership',
                    'currency' => 'CNY',
                    'price' => 0,
                    'compare_at_price' => null,
                    'stock' => null,
                    'summary' => '免费演示商品，用来测试零元下单、自动确认和会员中心订单展示。',
                    'content' => '零元商品不走支付流程。',
                    'payload' => ['demo' => true, 'kind' => 'free-bundle'],
                    'published_at' => now()->subDays(2),
                ],
            ),
        ];

        $events = [
            'ai-content-salon-2026' => Event::query()->updateOrCreate(
                ['slug' => 'ai-content-salon-2026'],
                [
                    'title' => 'AI 内容沙龙 2026',
                    'status' => 'registration-open',
                    'cover_image_url' => 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=1200&q=80',
                    'location' => '上海 · 徐汇西岸',
                    'is_paid' => false,
                    'price' => 0,
                    'required_member_group_id' => null,
                    'starts_at' => now()->addDays(9),
                    'ends_at' => now()->addDays(9)->addHours(4),
                    'registration_opens_at' => now()->subDays(3),
                    'registration_closes_at' => now()->addDays(7),
                    'capacity' => 120,
                    'summary' => '免费公开活动，用来测试无需支付的报名确认流程。',
                    'content' => '面向公开用户的内容运营线下活动。',
                    'payload' => ['demo' => true],
                ],
            ),
            'membership-growth-retreat' => Event::query()->updateOrCreate(
                ['slug' => 'membership-growth-retreat'],
                [
                    'title' => '会员增长闭门会',
                    'status' => 'registration-open',
                    'cover_image_url' => 'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?auto=format&fit=crop&w=1200&q=80',
                    'location' => '杭州 · 良渚数字中心',
                    'is_paid' => true,
                    'price' => 299,
                    'required_member_group_id' => $premiumGroup->id,
                    'starts_at' => now()->addDays(18),
                    'ends_at' => now()->addDays(18)->addHours(6),
                    'registration_opens_at' => now()->subDays(2),
                    'registration_closes_at' => now()->addDays(14),
                    'capacity' => 36,
                    'summary' => '付费且限会员等级的活动，用来测试会员限制与支付报名流程。',
                    'content' => '面向进阶会员的付费活动示例。',
                    'payload' => ['demo' => true],
                ],
            ),
            'future-product-archive' => Event::query()->updateOrCreate(
                ['slug' => 'future-product-archive'],
                [
                    'title' => '未来产品档案展',
                    'status' => 'finished',
                    'cover_image_url' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1200&q=80',
                    'location' => '深圳 · 南山创意港',
                    'is_paid' => true,
                    'price' => 199,
                    'required_member_group_id' => null,
                    'starts_at' => now()->subDays(20),
                    'ends_at' => now()->subDays(20)->addHours(5),
                    'registration_opens_at' => now()->subDays(40),
                    'registration_closes_at' => now()->subDays(23),
                    'capacity' => 80,
                    'summary' => '已结束活动，用来展示历史活动和已完成报名记录。',
                    'content' => '适合在前台活动页展示 finished 状态。',
                    'payload' => ['demo' => true],
                ],
            ),
        ];

        $this->seedSubscriptionScenarios($users, $plans);
        $this->seedProductOrderScenarios($users, $products);
        $this->seedEventRegistrationScenarios($users, $events);
    }

    private function seedSubscriptionScenarios(array $users, array $plans): void
    {
        $paidOrder = Order::query()->updateOrCreate(
            ['order_no' => 'DEMO-SUB-PAID-001'],
            [
                'user_id' => $users['linmei']->id,
                'order_type' => 'membership',
                'purchasable_type' => MembershipPlan::class,
                'purchasable_id' => $plans['pro-annual']->id,
                'title' => $plans['pro-annual']->name,
                'currency' => 'CNY',
                'amount' => $plans['pro-annual']->price,
                'status' => 'paid',
                'paid_at' => now()->subDays(6),
                'meta' => ['demo' => true, 'source' => 'seed'],
            ],
        );

        Payment::query()->updateOrCreate(
            ['provider_payment_no' => 'DEMO-PAY-SUB-001'],
            [
                'order_id' => $paidOrder->id,
                'provider' => 'alipay',
                'status' => 'paid',
                'amount' => $paidOrder->amount,
                'payload' => ['demo' => true],
                'paid_at' => now()->subDays(6),
            ],
        );

        UserSubscription::query()->updateOrCreate(
            ['user_id' => $users['linmei']->id, 'plan_id' => $plans['pro-annual']->id],
            [
                'last_order_id' => $paidOrder->id,
                'status' => 'active',
                'auto_renew' => true,
                'started_at' => now()->subDays(6),
                'expires_at' => now()->addDays(359),
                'meta' => ['demo' => true],
            ],
        );

        $pendingOrder = Order::query()->updateOrCreate(
            ['order_no' => 'DEMO-SUB-PENDING-001'],
            [
                'user_id' => $users['sunhao']->id,
                'order_type' => 'membership',
                'purchasable_type' => MembershipPlan::class,
                'purchasable_id' => $plans['growth-monthly']->id,
                'title' => $plans['growth-monthly']->name,
                'currency' => 'CNY',
                'amount' => $plans['growth-monthly']->price,
                'status' => 'pending',
                'paid_at' => null,
                'meta' => ['demo' => true, 'source' => 'seed'],
            ],
        );

        Payment::query()->updateOrCreate(
            ['provider_payment_no' => 'DEMO-PAY-SUB-002'],
            [
                'order_id' => $pendingOrder->id,
                'provider' => 'wechat',
                'status' => 'pending',
                'amount' => $pendingOrder->amount,
                'payload' => ['demo' => true],
                'paid_at' => null,
            ],
        );

        UserSubscription::query()->updateOrCreate(
            ['user_id' => $users['sunhao']->id, 'plan_id' => $plans['growth-monthly']->id],
            [
                'last_order_id' => $pendingOrder->id,
                'status' => 'pending',
                'auto_renew' => false,
                'started_at' => null,
                'expires_at' => null,
                'meta' => ['demo' => true],
            ],
        );

        $closedOrder = Order::query()->updateOrCreate(
            ['order_no' => 'DEMO-SUB-CLOSED-001'],
            [
                'user_id' => $users['chenyu']->id,
                'order_type' => 'membership',
                'purchasable_type' => MembershipPlan::class,
                'purchasable_id' => $plans['growth-monthly']->id,
                'title' => $plans['growth-monthly']->name,
                'currency' => 'CNY',
                'amount' => $plans['growth-monthly']->price,
                'status' => 'closed',
                'paid_at' => null,
                'meta' => ['demo' => true, 'source' => 'seed'],
            ],
        );

        Payment::query()->updateOrCreate(
            ['provider_payment_no' => 'DEMO-PAY-SUB-003'],
            [
                'order_id' => $closedOrder->id,
                'provider' => 'paypal',
                'status' => 'closed',
                'amount' => $closedOrder->amount,
                'payload' => ['demo' => true],
                'paid_at' => null,
            ],
        );

        UserSubscription::query()->updateOrCreate(
            ['user_id' => $users['chenyu']->id, 'plan_id' => $plans['growth-monthly']->id],
            [
                'last_order_id' => $closedOrder->id,
                'status' => 'cancelled',
                'auto_renew' => false,
                'started_at' => now()->subDays(45),
                'expires_at' => now()->subDays(15),
                'meta' => ['demo' => true],
            ],
        );
    }

    private function seedProductOrderScenarios(array $users, array $products): void
    {
        $paidOrder = Order::query()->updateOrCreate(
            ['order_no' => 'DEMO-PRD-PAID-001'],
            [
                'user_id' => $users['linmei']->id,
                'order_type' => 'product',
                'purchasable_type' => Product::class,
                'purchasable_id' => $products['techsir-notebook-kit']->id,
                'title' => $products['techsir-notebook-kit']->title,
                'currency' => 'CNY',
                'amount' => $products['techsir-notebook-kit']->price,
                'status' => 'paid',
                'paid_at' => now()->subDays(3),
                'meta' => ['demo' => true, 'source' => 'seed'],
            ],
        );

        Payment::query()->updateOrCreate(
            ['provider_payment_no' => 'DEMO-PAY-PRD-001'],
            [
                'order_id' => $paidOrder->id,
                'provider' => 'stripe',
                'status' => 'paid',
                'amount' => $paidOrder->amount,
                'payload' => ['demo' => true],
                'paid_at' => now()->subDays(3),
            ],
        );

        $pendingOrder = Order::query()->updateOrCreate(
            ['order_no' => 'DEMO-PRD-PENDING-001'],
            [
                'user_id' => $users['sunhao']->id,
                'order_type' => 'product',
                'purchasable_type' => Product::class,
                'purchasable_id' => $products['future-lab-playbook']->id,
                'title' => $products['future-lab-playbook']->title,
                'currency' => 'CNY',
                'amount' => $products['future-lab-playbook']->price,
                'status' => 'pending',
                'paid_at' => null,
                'meta' => ['demo' => true, 'source' => 'seed'],
            ],
        );

        Payment::query()->updateOrCreate(
            ['provider_payment_no' => 'DEMO-PAY-PRD-002'],
            [
                'order_id' => $pendingOrder->id,
                'provider' => 'wechat',
                'status' => 'processing',
                'amount' => $pendingOrder->amount,
                'payload' => ['demo' => true],
                'paid_at' => null,
            ],
        );

        Order::query()->updateOrCreate(
            ['order_no' => 'DEMO-PRD-FREE-001'],
            [
                'user_id' => $users['chenyu']->id,
                'order_type' => 'product',
                'purchasable_type' => Product::class,
                'purchasable_id' => $products['starter-member-onboarding']->id,
                'title' => $products['starter-member-onboarding']->title,
                'currency' => 'CNY',
                'amount' => 0,
                'status' => 'paid',
                'paid_at' => now()->subDay(),
                'meta' => ['demo' => true, 'source' => 'seed'],
            ],
        );
    }

    private function seedEventRegistrationScenarios(array $users, array $events): void
    {
        EventRegistration::query()->updateOrCreate(
            ['event_id' => $events['ai-content-salon-2026']->id, 'user_id' => $users['chenyu']->id],
            [
                'order_id' => null,
                'status' => 'approved',
                'payment_status' => 'not_required',
                'notes' => '免费活动，自动确认报名。',
                'payload' => ['demo' => true],
            ],
        );

        $paidOrder = Order::query()->updateOrCreate(
            ['order_no' => 'DEMO-EVT-PAID-001'],
            [
                'user_id' => $users['linmei']->id,
                'order_type' => 'event',
                'purchasable_type' => Event::class,
                'purchasable_id' => $events['membership-growth-retreat']->id,
                'title' => $events['membership-growth-retreat']->title,
                'currency' => 'CNY',
                'amount' => $events['membership-growth-retreat']->price,
                'status' => 'paid',
                'paid_at' => now()->subDays(2),
                'meta' => ['demo' => true, 'source' => 'seed'],
            ],
        );

        Payment::query()->updateOrCreate(
            ['provider_payment_no' => 'DEMO-PAY-EVT-001'],
            [
                'order_id' => $paidOrder->id,
                'provider' => 'alipay',
                'status' => 'paid',
                'amount' => $paidOrder->amount,
                'payload' => ['demo' => true],
                'paid_at' => now()->subDays(2),
            ],
        );

        EventRegistration::query()->updateOrCreate(
            ['event_id' => $events['membership-growth-retreat']->id, 'user_id' => $users['linmei']->id],
            [
                'order_id' => $paidOrder->id,
                'status' => 'approved',
                'payment_status' => 'paid',
                'notes' => '已支付并确认席位。',
                'payload' => ['demo' => true],
            ],
        );

        $pendingOrder = Order::query()->updateOrCreate(
            ['order_no' => 'DEMO-EVT-PENDING-001'],
            [
                'user_id' => $users['sunhao']->id,
                'order_type' => 'event',
                'purchasable_type' => Event::class,
                'purchasable_id' => $events['membership-growth-retreat']->id,
                'title' => $events['membership-growth-retreat']->title,
                'currency' => 'CNY',
                'amount' => $events['membership-growth-retreat']->price,
                'status' => 'pending',
                'paid_at' => null,
                'meta' => ['demo' => true, 'source' => 'seed'],
            ],
        );

        Payment::query()->updateOrCreate(
            ['provider_payment_no' => 'DEMO-PAY-EVT-002'],
            [
                'order_id' => $pendingOrder->id,
                'provider' => 'paypal',
                'status' => 'pending',
                'amount' => $pendingOrder->amount,
                'payload' => ['demo' => true],
                'paid_at' => null,
            ],
        );

        EventRegistration::query()->updateOrCreate(
            ['event_id' => $events['membership-growth-retreat']->id, 'user_id' => $users['sunhao']->id],
            [
                'order_id' => $pendingOrder->id,
                'status' => 'registered',
                'payment_status' => 'pending',
                'notes' => '待支付，方便测试支付回写。',
                'payload' => ['demo' => true],
            ],
        );

        $finishedOrder = Order::query()->updateOrCreate(
            ['order_no' => 'DEMO-EVT-HISTORY-001'],
            [
                'user_id' => $users['chenyu']->id,
                'order_type' => 'event',
                'purchasable_type' => Event::class,
                'purchasable_id' => $events['future-product-archive']->id,
                'title' => $events['future-product-archive']->title,
                'currency' => 'CNY',
                'amount' => $events['future-product-archive']->price,
                'status' => 'paid',
                'paid_at' => now()->subDays(21),
                'meta' => ['demo' => true, 'source' => 'seed'],
            ],
        );

        Payment::query()->updateOrCreate(
            ['provider_payment_no' => 'DEMO-PAY-EVT-003'],
            [
                'order_id' => $finishedOrder->id,
                'provider' => 'stripe',
                'status' => 'paid',
                'amount' => $finishedOrder->amount,
                'payload' => ['demo' => true],
                'paid_at' => now()->subDays(21),
            ],
        );

        EventRegistration::query()->updateOrCreate(
            ['event_id' => $events['future-product-archive']->id, 'user_id' => $users['chenyu']->id],
            [
                'order_id' => $finishedOrder->id,
                'status' => 'approved',
                'payment_status' => 'paid',
                'notes' => '历史已完成活动。',
                'payload' => ['demo' => true],
            ],
        );
    }
}
