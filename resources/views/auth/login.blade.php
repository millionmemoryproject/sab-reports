<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in · Million Memory Project Reporting</title>

    <link rel="stylesheet" href="{{ asset('css/reports/reporting.css') }}">
</head>
<body>
<div class="auth-shell">
    <div class="auth-card card">
        <div class="auth-brand">Million Memory Project Reporting</div>
        <p class="muted auth-sub">Sign in to continue</p>

        @if($errors->any())
            <div class="auth-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}">
            @csrf

            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>

            <label for="password">Password</label>
            <div class="password-field">
                <input id="password" type="text" class="password-input" name="password" required
                       autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false">
                <button type="button" class="password-toggle" aria-label="Show password" aria-pressed="false">Show</button>
            </div>

            <label class="checkbox-label auth-remember">
                <input type="checkbox" name="remember"> Remember me
            </label>

            <button type="submit" class="button auth-submit">Sign in</button>
        </form>

        <p class="auth-alt"><a href="{{ route('password.request') }}">Forgot your password?</a></p>
    </div>
</div>

<script src="{{ asset('js/reports/password-toggle.js') }}"></script>
</body>
</html>
