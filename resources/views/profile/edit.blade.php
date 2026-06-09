@extends('layouts.reporting', ['title' => 'Profile'])

@section('content')
    <section class="hero">
        <div class="hero-label">Account</div>
        <h1>Your Profile</h1>
        <p>Manage your account details and password.</p>
    </section>

    <div class="grid-two">
        <div class="card">
            <h2 class="section-title">Profile details</h2>

            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')

                <label for="name">Name</label>
                <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required>
                @error('name') <div class="auth-error">{{ $message }}</div> @enderror

                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required>
                @error('email') <div class="auth-error">{{ $message }}</div> @enderror

                <button type="submit" class="button auth-submit">Save profile</button>
            </form>
        </div>

        <div class="card">
            <h2 class="section-title">Change password</h2>

            <form method="POST" action="{{ route('profile.password') }}">
                @csrf
                @method('PUT')

                <label for="current_password">Current password</label>
                <div class="password-field">
                    <input id="current_password" type="text" class="password-input" name="current_password" required
                           autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false">
                    <button type="button" class="password-toggle" aria-label="Show password" aria-pressed="false">Show</button>
                </div>
                @error('current_password') <div class="auth-error">{{ $message }}</div> @enderror

                <label for="password">New password</label>
                <div class="password-field">
                    <input id="password" type="text" class="password-input js-pw-strength" name="password" required
                           autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false">
                    <button type="button" class="password-toggle" aria-label="Show password" aria-pressed="false">Show</button>
                </div>
                <div class="pw-meter">
                    <div class="pw-meter-track"><div class="pw-meter-fill"></div></div>
                    <span class="pw-meter-label"></span>
                </div>
                @error('password') <div class="auth-error">{{ $message }}</div> @enderror

                <label for="password_confirmation">Confirm new password</label>
                <div class="password-field">
                    <input id="password_confirmation" type="text" class="password-input" name="password_confirmation" required
                           autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false">
                    <button type="button" class="password-toggle" aria-label="Show password" aria-pressed="false">Show</button>
                </div>

                <button type="submit" class="button auth-submit">Update password</button>
            </form>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/reports/password-toggle.js') }}"></script>
        <script src="{{ asset('js/reports/password-strength.js') }}"></script>
    @endpush
@endsection
