@extends(\App\Support\SiteTheme::view('layout', 'themes.default.layout'), ['title' => '修改资料'])

@section('content')
    @include(\App\Support\SiteTheme::view('member.partials.topbar', 'site.member.partials.topbar'))

    <div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
        @include(\App\Support\SiteTheme::view('member.partials.nav', 'site.member.partials.nav'))

        <div class="space-y-6">
            @include(\App\Support\SiteTheme::view('member.partials.page-header', 'site.member.partials.page-header'), [
                'eyebrow' => 'Profile',
                'title' => '修改资料',
                'description' => '这里集中管理用户名、邮箱等基础资料，后面可以继续扩展头像、偏好设置和安全信息。',
            ])

            @php
                $avatarLarge = old('avatar_preview_large', $user->avatarUrl('large'));
                $avatarMedium = old('avatar_preview_medium', $user->avatarUrl('medium'));
                $avatarSmall = old('avatar_preview_small', $user->avatarUrl('small'));
            @endphp

            <form method="POST" action="{{ route('member.profile.update') }}" enctype="multipart/form-data" class="rounded-[32px] border border-slate-200/80 bg-white/96 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)]" data-avatar-form>
                @csrf
                @method('PUT')

                <div class="grid gap-5 lg:grid-cols-2">
                    <div class="space-y-5">
                        <div class="rounded-[24px] border border-slate-200 bg-slate-50/80 p-5">
                            <p class="text-sm font-semibold text-slate-900">基础资料</p>
                            <div class="mt-4 space-y-4">
                                <div class="grid items-center gap-3 sm:grid-cols-[68px_minmax(0,1fr)]">
                                    <label for="username" class="text-sm font-medium text-slate-700">用户名</label>
                                    <input id="username" name="username" type="text" value="{{ old('username', $user->username) }}" class="w-full rounded-[18px] border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sky-400 focus:bg-white">
                                </div>
                                <div class="grid items-center gap-3 sm:grid-cols-[68px_minmax(0,1fr)]">
                                    <label for="email" class="text-sm font-medium text-slate-700">邮箱</label>
                                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" class="w-full rounded-[18px] border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sky-400 focus:bg-white">
                                </div>
                                <div class="grid items-center gap-3 sm:grid-cols-[68px_minmax(0,1fr)]">
                                    <label for="first_name" class="text-sm font-medium text-slate-700">名字</label>
                                    <input id="first_name" name="first_name" type="text" value="{{ old('first_name', $user->first_name) }}" class="w-full rounded-[18px] border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sky-400 focus:bg-white">
                                </div>
                                <div class="grid items-center gap-3 sm:grid-cols-[68px_minmax(0,1fr)]">
                                    <label for="last_name" class="text-sm font-medium text-slate-700">姓氏</label>
                                    <input id="last_name" name="last_name" type="text" value="{{ old('last_name', $user->last_name) }}" class="w-full rounded-[18px] border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sky-400 focus:bg-white">
                                </div>

                                <div class="border-t border-slate-200 pt-4 space-y-4">
                                    <div class="grid items-center gap-3 sm:grid-cols-[68px_minmax(0,1fr)]">
                                        <label for="nickname" class="text-sm font-medium text-slate-700">公开昵称</label>
                                        <input id="nickname" name="nickname" type="text" value="{{ old('nickname', $user->nickname) }}" class="w-full rounded-[18px] border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sky-400 focus:bg-white" placeholder="可自动生成，也可自己输入或清空" data-nickname-input>
                                    </div>
                                    <div class="grid items-center gap-3 sm:grid-cols-[68px_minmax(0,1fr)]">
                                        <label for="nickname_strategy" class="text-sm font-medium text-slate-700">昵称方式</label>
                                        <select id="nickname_strategy" name="nickname_strategy" class="w-full rounded-[18px] border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sky-400 focus:bg-white" data-nickname-strategy>
                                            @php($nicknameStrategy = old('nickname_strategy', 'manual'))
                                            <option value="manual" @selected($nicknameStrategy === 'manual')>手动输入</option>
                                            <option value="username" @selected($nicknameStrategy === 'username')>使用用户名</option>
                                            <option value="last_first" @selected($nicknameStrategy === 'last_first')>姓在前 名在后</option>
                                            <option value="first_last" @selected($nicknameStrategy === 'first_last')>名在前 姓在后</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[24px] border border-slate-200 bg-slate-50/80 p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div><p class="text-sm font-semibold text-slate-900">头像上传</p></div>
                            <label for="avatar" class="inline-flex cursor-pointer items-center justify-center rounded-[14px] border border-sky-200 bg-sky-50 px-4 py-2.5 text-sm font-medium text-sky-700 transition hover:border-sky-300 hover:bg-sky-100">上传头像</label>
                            <input id="avatar" name="avatar" type="file" accept="image/*" class="hidden" data-avatar-input>
                        </div>

                        <div class="mt-4 grid gap-4 md:grid-cols-[minmax(0,1fr)_180px]">
                            <div class="rounded-[18px] border border-slate-200 bg-white p-4">
                                <p class="text-sm font-medium text-slate-800">大尺寸预览</p>
                                <div class="mt-3 flex aspect-square w-full max-w-[220px] items-center justify-center overflow-hidden rounded-[20px] border border-dashed border-slate-300 bg-slate-50" data-avatar-preview-large-wrap>
                                    @if ($avatarLarge)
                                        <img src="{{ $avatarLarge }}" alt="头像预览" class="h-full w-full object-cover" data-avatar-preview-large>
                                    @else
                                        <span class="text-sm text-slate-400" data-avatar-placeholder-large>上传后这里显示大尺寸头像</span>
                                    @endif
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div class="rounded-[16px] border border-slate-200 bg-white p-3.5">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Large 256x256</p>
                                    <div class="mt-2.5 flex h-20 w-20 items-center justify-center overflow-hidden rounded-[16px] border border-slate-200 bg-slate-50" data-avatar-preview-large-card>
                                        @if ($avatarLarge)
                                            <img src="{{ $avatarLarge }}" alt="大尺寸头像" class="h-full w-full object-cover" data-avatar-preview-large-card-image>
                                        @else
                                            <span class="text-xs text-slate-400">大</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="rounded-[16px] border border-slate-200 bg-white p-3.5">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Medium 120x120</p>
                                    <div class="mt-2.5 flex h-14 w-14 items-center justify-center overflow-hidden rounded-[14px] border border-slate-200 bg-slate-50" data-avatar-preview-medium>
                                        @if ($avatarMedium)
                                            <img src="{{ $avatarMedium }}" alt="中尺寸头像" class="h-full w-full object-cover" data-avatar-preview-medium-image>
                                        @else
                                            <span class="text-xs text-slate-400">中</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="rounded-[16px] border border-slate-200 bg-white p-3.5">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Small 48x48</p>
                                    <div class="mt-2.5 flex h-11 w-11 items-center justify-center overflow-hidden rounded-[12px] border border-slate-200 bg-slate-50" data-avatar-preview-small>
                                        @if ($avatarSmall)
                                            <img src="{{ $avatarSmall }}" alt="小尺寸头像" class="h-full w-full object-cover" data-avatar-preview-small-image>
                                        @else
                                            <span class="text-xs text-slate-400">小</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-5 max-w-[520px]">
                    <div class="grid items-center gap-3 sm:grid-cols-[68px_minmax(0,1fr)]">
                        <label for="current_password" class="text-sm font-medium text-slate-700">当前密码</label>
                        <input id="current_password" name="current_password" type="password" class="w-full rounded-[18px] border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sky-400 focus:bg-white" placeholder="仅在修改邮箱时需要输入">
                    </div>
                </div>

                <div class="mt-5 max-w-[860px]">
                    <div class="grid gap-3 sm:grid-cols-[68px_minmax(0,1fr)] sm:items-start">
                        <label for="bio" class="pt-3 text-sm font-medium text-slate-700">自我简介</label>
                        <textarea id="bio" name="bio" rows="4" class="w-full rounded-[18px] border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-7 text-slate-900 outline-none transition focus:border-sky-400 focus:bg-white" placeholder="介绍一下你关注的领域、擅长的方向，或者希望前台作者卡片展示的一段自我说明。">{{ old('bio', $user->bio) }}</textarea>
                    </div>
                </div>

                <div class="mt-5 rounded-[24px] border border-slate-200 bg-slate-50/80 p-5">
                    <p class="text-sm font-semibold text-slate-900">账户摘要</p>
                    <div class="mt-4 grid gap-4 md:grid-cols-3">
                        <div>
                            <div class="text-xs uppercase tracking-[0.18em] text-slate-400">账户 ID</div>
                            <div class="mt-2 font-semibold text-slate-900">ID-{{ str_pad((string) $user->id, 8, '0', STR_PAD_LEFT) }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-[0.18em] text-slate-400">会员组</div>
                            <div class="mt-2 font-semibold text-slate-900">{{ $user->memberGroup?->name ?? '未分组' }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-[0.18em] text-slate-400">状态</div>
                            <div class="mt-2 font-semibold text-emerald-700">{{ $user->status === 'active' ? '在线' : '非活跃' }}</div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="inline-flex items-center justify-center rounded-[18px] bg-gradient-to-r from-sky-500 to-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-sky-500/20 transition hover:from-blue-600 hover:to-slate-900">保存资料</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var form = document.querySelector('[data-avatar-form]');
            var input = form?.querySelector('[data-avatar-input]');
            var nicknameStrategy = form?.querySelector('[data-nickname-strategy]');
            var nicknameInput = form?.querySelector('[data-nickname-input]');
            var firstNameInput = form?.querySelector('#first_name');
            var lastNameInput = form?.querySelector('#last_name');
            var usernameInput = form?.querySelector('#username');

            if (!form) {
                return;
            }

            var previewTargets = [
                form.querySelector('[data-avatar-preview-large]'),
                form.querySelector('[data-avatar-preview-large-card-image]'),
                form.querySelector('[data-avatar-preview-medium-image]'),
                form.querySelector('[data-avatar-preview-small-image]')
            ];

            function ensureImage(containerSelector, imageSelector) {
                var container = form.querySelector(containerSelector);
                var image = form.querySelector(imageSelector);

                if (!container || image) {
                    return image;
                }

                container.innerHTML = '';
                image = document.createElement('img');
                image.alt = '头像预览';
                image.className = 'h-full w-full object-cover';
                image.setAttribute(imageSelector.slice(1, -1), '');
                container.appendChild(image);

                return image;
            }

            if (input) {
                input.addEventListener('change', function (event) {
                    var file = event.target.files && event.target.files[0];

                    if (!file) {
                        return;
                    }

                    var reader = new FileReader();

                    reader.onload = function (loadEvent) {
                        var largeMain = form.querySelector('[data-avatar-preview-large]') || ensureImage('[data-avatar-preview-large-wrap]', '[data-avatar-preview-large]');
                        var largeCard = form.querySelector('[data-avatar-preview-large-card-image]') || ensureImage('[data-avatar-preview-large-card]', '[data-avatar-preview-large-card-image]');
                        var medium = form.querySelector('[data-avatar-preview-medium-image]') || ensureImage('[data-avatar-preview-medium]', '[data-avatar-preview-medium-image]');
                        var small = form.querySelector('[data-avatar-preview-small-image]') || ensureImage('[data-avatar-preview-small]', '[data-avatar-preview-small-image]');

                        [largeMain, largeCard, medium, small].forEach(function (image) {
                            if (image) {
                                image.src = loadEvent.target.result;
                            }
                        });

                        form.querySelector('[data-avatar-placeholder-large]')?.remove();
                    };

                    reader.readAsDataURL(file);
                });
            }

            function suggestedNickname() {
                var strategy = nicknameStrategy ? nicknameStrategy.value : 'manual';
                var username = (usernameInput?.value || '').trim();
                var firstName = (firstNameInput?.value || '').trim();
                var lastName = (lastNameInput?.value || '').trim();

                if (strategy === 'username') {
                    return username;
                }

                if (strategy === 'last_first') {
                    return [lastName, firstName].filter(Boolean).join(' ').trim();
                }

                if (strategy === 'first_last') {
                    return [firstName, lastName].filter(Boolean).join(' ').trim();
                }

                return nicknameInput ? nicknameInput.value : '';
            }

            function applyNicknameSuggestion() {
                if (!nicknameInput || !nicknameStrategy || nicknameStrategy.value === 'manual') {
                    return;
                }

                nicknameInput.value = suggestedNickname();
            }

            [nicknameStrategy, firstNameInput, lastNameInput, usernameInput].forEach(function (field) {
                if (!field) {
                    return;
                }

                field.addEventListener('change', applyNicknameSuggestion);
                field.addEventListener('input', applyNicknameSuggestion);
            });
        });
    </script>
@endsection
