<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset password · Million Memory Project Reporting</title>

    <link rel="stylesheet" href="{{ asset('css/reports/reporting.css') }}">
</head>
<body>
<div class="auth-shell">
    <div class="auth-card card">
        <div class="auth-brand">Million Memory Project Reporting</div>
        <p class="muted auth-sub">Forgot your password? Enter your email and we'll send a reset link.</p>

        @if(session('status'))
            <div class="auth-notice">{{ session('status') }}</div>
        @endif

        @if($errors->any())
            <div class="auth-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>

            <button type="submit" class="button auth-submit">Email reset link</button>
        </form>

        <p class="auth-alt"><a href="{{ route('login') }}">Back to sign in</a></p>
    </div>
</div>
</body>
</html>
