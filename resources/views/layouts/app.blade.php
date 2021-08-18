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
        window.user_id = {{ Auth::id() ?? 0 }};
        window.user_admin = {{ Auth::user()->isAdmin() ? 1 : 0}};
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
                University of Glasgow, School of Engineering Exam Database<br />
                You can leave feedback about the new system using
                <a target="_blank" rel="noopener noreferrer" href="https://gla-my.sharepoint.com/:x:/r/personal/suzanne_robertson_glasgow_ac_uk/_layouts/15/guestaccess.aspx?e=4%3Ap1ogVo&at=9&CID=0b5ea03f-5873-772c-15c4-4cdd7b45401b&share=ERlE3RkeyM5Ps23YODTxXRgBPF5bsWJ793dFQ78j0FHrWg">
                    this Office365 spreadsheet.
                </a>
            </div>
        </div>


    </div>

    @livewireScripts
    <script src="{{ asset('js/app.js') }}"></script>

    @stack('scripts')

    <!-- {{ session('academic_session') }} -->
</body>

</html>
