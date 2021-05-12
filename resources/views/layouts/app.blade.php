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
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    @livewireStyles

    <script>
        window.user_id = {{ Auth::id() }};
        window.user_admin = @json(Auth::user()->isAdmin() ?? false);
        @if (isset($course))
            window.is_external = {{ Auth::check() && Auth::user()->isExternalFor($course) ? 1 : 0 }};
        @else
            window.is_external = {{ Auth::check() && Auth::user()->isExternal() ? 1 : 0 }};
        @endif
    </script>
    <!-- Routes -->
    @routes
</head>

<body>
    <div id="layout">
        @include('layouts.navbar')

        <section class="section" id="app">
            @if (session()->has('success'))
                <div class="success-box shadow-lg notification is-success">
                    {{ session('success') }}
                </div>
            @endif
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
                University of Glasgow, College of Science &amp; Engineering Exam Database<br />
            </div>
        </div>


    </div>

    @livewireScripts
    <script src="{{ mix('js/app.js') }}"></script>

    @stack('scripts')
</body>

</html>
