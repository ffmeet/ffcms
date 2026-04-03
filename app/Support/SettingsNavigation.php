<?php

namespace App\Support;

use App\Filament\Pages\SettingsCenter;
use App\Filament\Resources\ContentModels\ContentModelResource;
use App\Filament\Resources\MemberGroups\MemberGroupResource;

class SettingsNavigation
{
    public static function sections(): array
    {
        return [
            [
                'heading' => '常规',
                'items' => [
                    ['label' => '设置总览', 'icon' => 'heroicon-o-cog-6-tooth', 'description' => '统一查看所有低频配置入口。', 'url' => SettingsCenter::getUrl()],
                    ['label' => '站点名称与描述', 'icon' => 'heroicon-o-identification', 'description' => '统一管理站点标题、描述和品牌信息。'],
                    ['label' => 'Logo 与 Favicon', 'icon' => 'heroicon-o-photo', 'description' => '维护站点标识、页签图标与品牌露出。'],
                    ['label' => '时区与语言', 'icon' => 'heroicon-o-globe-alt', 'description' => '设置后台默认时区、语言和时间展示规则。'],
                ],
            ],
            [
                'heading' => '设计',
                'items' => [
                    ['label' => '设计与品牌', 'icon' => 'heroicon-o-swatch', 'description' => '首页视觉、品牌色、页脚与公告条。'],
                    ['label' => '导航', 'icon' => 'heroicon-o-bars-3-bottom-left', 'description' => '顶部导航、底部导航和菜单顺序。'],
                    ['label' => '首页布局', 'icon' => 'heroicon-o-squares-2x2', 'description' => '首页推荐位、频道区块和展示节奏。'],
                ],
            ],
            [
                'heading' => '内容设置',
                'items' => [
                    ['label' => '内容模型', 'icon' => 'heroicon-o-circle-stack', 'description' => '管理文章、快讯等内容模型与扩展字段。', 'url' => ContentModelResource::getUrl()],
                    ['label' => '发布规则', 'icon' => 'heroicon-o-paper-airplane', 'description' => '规划默认发布时间、状态、Slug 和投稿规则。'],
                    ['label' => '快讯设置', 'icon' => 'heroicon-o-bolt', 'description' => '集中管理快讯的发布体验与展示规则。'],
                ],
            ],
            [
                'heading' => '会员设置',
                'items' => [
                    ['label' => '会员组', 'icon' => 'heroicon-o-user-group', 'description' => '管理会员分组、等级与访问能力。', 'url' => MemberGroupResource::getUrl()],
                    ['label' => '登录与注册', 'icon' => 'heroicon-o-arrow-right-end-on-rectangle', 'description' => '维护会员入口、欢迎信息和通知设置。'],
                ],
            ],
        ];
    }
}
