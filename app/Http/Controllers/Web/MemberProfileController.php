<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\SiteTheme;
use App\Support\AvatarManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MemberProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view(SiteTheme::view('member.profile-edit', 'themes.default.member.profile-edit'), [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request, AvatarManager $avatarManager): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'first_name' => ['nullable', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'nickname' => ['nullable', 'string', 'max:120'],
            'nickname_strategy' => ['nullable', 'in:manual,username,last_first,first_last'],
            'bio' => ['nullable', 'string', 'max:1200'],
            'current_password' => ['nullable', 'string'],
            'avatar' => ['nullable', 'image', 'max:5120'],
        ]);

        if ($validated['email'] !== $user->email) {
            $request->validate([
                'current_password' => ['required', 'current_password'],
            ], [
                'current_password.required' => '修改邮箱前需要先输入当前密码。',
                'current_password.current_password' => '当前密码不正确，暂时不能修改邮箱。',
            ]);
        }

        $nicknameStrategy = $validated['nickname_strategy'] ?? 'manual';
        $nickname = blank($validated['nickname'] ?? null)
            ? User::resolveNickname(
                $nicknameStrategy,
                $validated['first_name'] ?? null,
                $validated['last_name'] ?? null,
                null,
                $validated['username'] ?? $user->username,
            )
            : trim((string) $validated['nickname']);

        $data = [
            'username' => $validated['username'],
            'email' => $validated['email'],
            'first_name' => blank($validated['first_name'] ?? null) ? null : trim((string) $validated['first_name']),
            'last_name' => blank($validated['last_name'] ?? null) ? null : trim((string) $validated['last_name']),
            'nickname' => $nickname !== '' ? $nickname : null,
            'bio' => blank($validated['bio'] ?? null) ? null : trim((string) $validated['bio']),
        ];

        if ($request->hasFile('avatar')) {
            $data = array_merge($data, $avatarManager->storeForUser($user, $request->file('avatar')));
        }

        $user->update($data);

        return redirect()
            ->route('member.profile.edit')
            ->with('status', '资料已更新。');
    }
}
