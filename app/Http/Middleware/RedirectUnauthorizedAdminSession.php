<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectUnauthorizedAdminSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && ! $user->canAccessPanel(Filament::getCurrentPanel())) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->to('/admin/login')
                ->with('status', '当前账号没有后台权限，请使用管理员账号登录。');
        }

        return $next($request);
    }
}
