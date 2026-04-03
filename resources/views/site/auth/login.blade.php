@extends('site.layout', ['title' => '前台登录'])

@section('content')
    <section class="card" style="max-width: 520px; margin: 0 auto;">
        <h1>前台登录</h1>
        <p class="muted">支持使用用户名或邮箱登录。</p>

        <form method="POST" action="{{ route('auth.login') }}">
            @csrf

            <label for="login">用户名或邮箱</label>
            <input id="login" name="login" type="text" value="{{ old('login') }}" required>

            <label for="password">密码</label>
            <input id="password" name="password" type="password" required>

            <label style="display:flex;align-items:center;gap:8px;margin-bottom:16px;font-weight:400;">
                <input type="checkbox" name="remember" value="1" style="width:auto;margin:0;">
                记住登录状态
            </label>

            <button class="primary" type="submit">登录</button>
        </form>
    </section>
@endsection
