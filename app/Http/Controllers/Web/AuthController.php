<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MemberGroup;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('site.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        foreach (['username', 'email'] as $field) {
            if (Auth::attempt([$field => $credentials['login'], 'password' => $credentials['password'], 'status' => 'active'], $remember)) {
                $request->session()->regenerate();

                return redirect()->intended(route('member.dashboard'));
            }
        }

        return back()
            ->withInput($request->except('password'))
            ->withErrors([
                'login' => '用户名、邮箱或密码不正确，或账号已停用。',
            ]);
    }

    public function showRegister(): View
    {
        return view('site.auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('users', 'username')],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $defaultGroupId = MemberGroup::query()
            ->firstOrCreate(
                ['name' => '默认会员'],
                [
                    'min_points' => 0,
                    'max_points' => 9999,
                    'permissions' => ['site.access' => true],
                ],
            )
            ->id;

        $user = User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password_hash' => $data['password'],
            'group_id' => $defaultGroupId,
            'status' => 'active',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('member.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('site.home');
    }
}
