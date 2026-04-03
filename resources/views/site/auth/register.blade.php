@extends('site.layout', ['title' => '前台注册'])

@section('content')
    <section class="card" style="max-width: 520px; margin: 0 auto;">
        <h1>前台注册</h1>
        <p class="muted">当前先提供基础会员注册能力，后续再扩展资料页和第三方登录。</p>

        <form method="POST" action="{{ route('auth.register') }}">
            @csrf

            <label for="username">用户名</label>
            <input id="username" name="username" type="text" value="{{ old('username') }}" required>

            <label for="email">邮箱</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required>

            <label for="password">密码</label>
            <input id="password" name="password" type="password" required>

            <label for="password_confirmation">确认密码</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required>

            <button class="primary" type="submit">注册并进入会员中心</button>
        </form>
    </section>
@endsection
