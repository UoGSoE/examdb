<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="{{ global_asset('css/app.css') }}" rel="stylesheet">

    <!-- Routes -->
    @routes

    @stack('head')
</head>
<body>
    <div id="app">
        @yield('content')
    </div>
    <footer style="position: fixed; bottom: 0px; width: 100%;" class="footer">
        <div class="content has-text-centered">
            University of Glasgow, School of Engineering Exam Database
        </div>
    </footer>
    <!-- Scripts -->
    <script src="{{ global_asset('js/app.js') }}" defer></script>
</body>
</html>
