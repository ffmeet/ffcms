<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMemberPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasMemberPermission($permission)) {
            return redirect()->route('site.home')
                ->with('status', '当前账号暂未开通该功能权限，请联系管理员或升级会员组。');
        }

        return $next($request);
    }
}
