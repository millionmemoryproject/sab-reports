<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Million Memory Project Reporting' }}</title>

    <link rel="stylesheet" href="{{ asset('css/reports/reporting.css') }}">

    @stack('head')
    @stack('styles')
</head>
<body>
<div class="report-shell">

    @include('layouts.navigation')

    @if(session('status'))
        <div class="status">
            {{ session('status') }}
        </div>
    @endif

    @yield('content')

</div>

<script src="{{ asset('js/reports/nav-menu.js') }}"></script>
@stack('scripts')
</body>
</html>