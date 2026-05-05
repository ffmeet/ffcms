<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\UsesSettingsShell;
use App\Support\OperationalHealth;
use App\Support\UploadLogReader;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class UploadDiagnosticsCenter extends Page
{
    use UsesSettingsShell;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCloudArrowUp;

    protected static ?string $title = '上传诊断';

    protected string $view = 'filament.pages.upload-diagnostics-center';

    public function getEntries(): array
    {
        $level = request()->string('level')->toString();
        $search = trim(request()->string('q')->toString());

        return UploadLogReader::recentEntries(
            limit: 120,
            level: $level !== '' ? $level : null,
            search: $search !== '' ? $search : null,
        )->all();
    }

    public function getSummaryCards(): array
    {
        $summary = UploadLogReader::summary();

        return [
            [
                'label' => '最近记录',
                'value' => (string) $summary['total'],
                'description' => '默认读取最近 200 条上传诊断事件。',
            ],
            [
                'label' => '失败 / 错误',
                'value' => (string) $summary['errors'],
                'description' => '明确异常或落盘失败事件。',
            ],
            [
                'label' => '警告',
                'value' => (string) $summary['warnings'],
                'description' => '如 PHP 上传上限拦截、请求未带文件等前置问题。',
            ],
            [
                'label' => '最近失败时间',
                'value' => $summary['latest_failed_at'] ?? '暂无',
                'description' => '便于快速判断问题是否刚刚发生。',
            ],
        ];
    }

    public function getOperationalCards(): array
    {
        return OperationalHealth::overview();
    }
}
