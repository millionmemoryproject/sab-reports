<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'SAB Reporting' }}</title>

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

@stack('scripts')
</body>
</html>