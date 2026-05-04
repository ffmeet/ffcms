<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\SiteTheme;
use Illuminate\Http\RedirectResponse;

class ThemePreviewController extends Controller
{
    public function preview(string $theme): RedirectResponse
    {
        abort_unless(array_key_exists($theme, SiteTheme::options()), 404);

        session([SiteTheme::PREVIEW_SESSION_KEY => $theme]);

        return redirect('/')
            ->with('status', '已进入 '.$theme.' 主题预览模式。');
    }

    public function reset(): RedirectResponse
    {
        session()->forget(SiteTheme::PREVIEW_SESSION_KEY);

        return redirect('/')
            ->with('status', '已退出主题预览模式。');
    }
}
