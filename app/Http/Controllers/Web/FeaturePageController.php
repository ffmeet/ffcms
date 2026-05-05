<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\SiteTheme;
use Illuminate\Contracts\View\View;

class FeaturePageController extends Controller
{
    public function shop(): View
    {
        return view(SiteTheme::view('pages.feature', 'themes.default.pages.feature'), [
            'title' => '商店系统 - 年度科技先生',
            'eyebrow' => 'SHOP',
            'heading' => '商店系统将基于更稳的商品底座接入。',
            'description' => '这一模块会优先保持商品、订单、支付底层通用，不先把系统锁死成纯数字商品，后续会结合更成熟的 Filament 生态方案评估实体商品与统一商店体验。',
            'highlights' => [
                '当前先保留通用商品、订单、支付底座，不继续深挖自造商城流程。',
                '后续会优先评估 Filament 官方生态或成熟插件，减少重复造轮子。',
                '商品能力会同时为数字权益、活动资格与未来实体商品预留扩展空间。',
            ],
        ]);
    }

    public function events(): View
    {
        return view(SiteTheme::view('pages.feature', 'themes.default.pages.feature'), [
            'title' => '活动系统 - 年度科技先生',
            'eyebrow' => 'EVENTS',
            'heading' => '活动系统将承接免费与付费活动。',
            'description' => '这一模块会统一支持活动列表、活动详情、报名确认、会员限制和付费活动报名流程。',
            'highlights' => [
                '支持免费报名与付费报名两种模式。',
                '活动可限制特定会员层级参与。',
                '报名记录与支付记录将共用同一套底座。',
            ],
        ]);
    }

    public function pricing(): View
    {
        return view(SiteTheme::view('pages.feature', 'themes.default.pages.feature'), [
            'title' => '会员计划 - 年度科技先生',
            'eyebrow' => 'MEMBERSHIP',
            'heading' => '会员与订阅体系正在搭建。',
            'description' => '首版会提供免费会员与多付费层，为内容权限、活动优先权和商店权益提供统一底座。',
            'highlights' => [
                '支持免费会员和多个付费层。',
                '默认规划微信支付与支付宝。',
                '后续会打通活动报名、专题内容和商店权益。',
            ],
        ]);
    }
}
