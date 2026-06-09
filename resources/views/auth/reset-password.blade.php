<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Choose a new password · Million Memory Project Reporting</title>

    <link rel="stylesheet" href="{{ asset('css/reports/reporting.css') }}">
</head>
<body>
<div class="auth-shell">
    <div class="auth-card card">
        <div class="auth-brand">Million Memory Project Reporting</div>
        <p class="muted auth-sub">Choose a new password</p>

        @if($errors->any())
            <div class="auth-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.store') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required>

            <label for="password">New password</label>
            <div class="password-field">
                <input id="password" type="text" class="password-input js-pw-strength" name="password" required autofocus
                       autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false">
                <button type="button" class="password-toggle" aria-label="Show password" aria-pressed="false">Show</button>
            </div>
            <div class="pw-meter">
                <div class="pw-meter-track"><div class="pw-meter-fill"></div></div>
                <span class="pw-meter-label"></span>
            </div>

            <label for="password_confirmation">Confirm new password</label>
            <div class="password-field">
                <input id="password_confirmation" type="text" class="password-input" name="password_confirmation" required
                       autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false">
                <button type="button" class="password-toggle" aria-label="Show password" aria-pressed="false">Show</button>
            </div>

            <button type="submit" class="button auth-submit">Reset password</button>
        </form>

        <p class="auth-alt"><a href="{{ route('login') }}">Back to sign in</a></p>
    </div>
</div>

<script src="{{ asset('js/reports/password-toggle.js') }}"></script>
<script src="{{ asset('js/reports/password-strength.js') }}"></script>
</body>
</html>
