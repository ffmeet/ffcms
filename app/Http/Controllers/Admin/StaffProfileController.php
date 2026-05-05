<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StaffProfileController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user && $user->is_staff_account, 403);

        $validated = $request->validateWithBag('staffProfile', [
            'display_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'nickname' => ['nullable', 'string', 'max:120'],
            'bio' => ['nullable', 'string', 'max:1200'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        $data = [
            'display_name' => trim((string) $validated['display_name']),
            'email' => trim((string) $validated['email']),
            'nickname' => blank($validated['nickname'] ?? null) ? null : trim((string) $validated['nickname']),
            'bio' => blank($validated['bio'] ?? null) ? null : trim((string) $validated['bio']),
        ];

        if (filled($validated['password'] ?? null)) {
            $data['password_hash'] = $validated['password'];
        }

        $user->update($data);

        return back()
            ->with('status', '员工个人资料已更新。');
    }
}
