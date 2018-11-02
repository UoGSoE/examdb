<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <script>
        window.user_id = {{ Auth::id() }};
        window.is_external = {{ Auth::check() && Auth::user()->isExternal() ? 1 : 0 }};
    </script>
    <!-- Routes -->
    @routes
</head>
<body>
    @include('layouts.navbar')

    <section class="section">
        <div id="app" class="container">
            @yield('content')

            <portal-target name="portal-modal">
            </portal-target>
        </div>
    </section>

    <div id="footer" class="footer">
        <div class="content has-text-centered">
            University of Glasgow, School of Engineering Exam Database
        </div>
    </div>
</body>
</html>
