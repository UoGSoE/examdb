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
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    @livewireStyles

    <script>
        window.user_id = {{ Auth::id() }};
        window.is_external = {{ Auth::check() && Auth::user()->isExternal() ? 1 : 0 }};
    </script>
    <!-- Routes -->
    @routes
</head>

<body>
    <div id="layout">
        @include('layouts.navbar')

        <section class="section" id="app">
            <div class="container">
                @yield('content')

                <portal-target name="portal-modal">
                </portal-target>
            </div>

            @impersonating
            <div class="box impersonation-box shadow-lg">
                <form method="GET" action="{{ route('impersonate.leave') }}">
                    @csrf
                    <button class="button is-outlined">Stop impersonating</button>
                </form>
            </div>
            @endImpersonating

        </section>

        <div id="footer" class="footer">
            <div class="content has-text-centered">
                University of Glasgow, School of Engineering Exam Database
            </div>
        </div>


    </div>

    @livewireScripts
    <script src="{{ asset('js/app.js') }}"></script>

    @stack('scripts')
</body>

</html>