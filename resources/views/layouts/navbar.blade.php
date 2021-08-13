<nav class="navbar is-info" role="navigation" aria-label="main navigation">
    <div class="navbar-brand">
        <a class="navbar-item has-text-weight-semibold" href="/">
            ExamDB
        </a>

        <a role="button" class="navbar-burger burger" aria-label="menu" aria-expanded="false" data-target="navbarBasicExample">
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
        </a>
    </div>

    <div id="navbarBasicExample" class="navbar-menu">
        <div class="navbar-start">

            @admin
            <div class="navbar-item has-dropdown is-hoverable">
                <a class="navbar-link">
                    Admin
                </a>

                <div class="navbar-dropdown">
                    <a class="navbar-item" href="{{ route('activity.index') }}">
                        Logs
                    </a>
                    <a class="navbar-item" href="{{ route('user.index') }}">
                        Users
                    </a>
                    <a class="navbar-item" href="{{ route('course.index') }}">
                        Courses
                    </a>
                    <a class="navbar-item" href="{{ route('paper.index') }}">
                        Papers
                    </a>
                    <hr class="navbar-divider">
                    <a class="navbar-item" href="{{ route('archive.index') }}">
                        Archives
                    </a>
                    <a class="navbar-item" href="{{ route('admin.options.edit') }}">
                        Options
                    </a>
                </div>
            </div>
            <div class="navbar-item has-dropdown is-hoverable">
                <a class="navbar-link">
                    Sessions ({{ session('academic_session') }})
                </a>

                <div class="navbar-dropdown">
                    <a class="navbar-item" href="{{ route('academicsession.edit') }}">
                        Manage Sessions
                    </a>
                    <hr class="navbar-divider">
                    @foreach ($navbarAcademicSessions as $session)
                        <form class="navbar-item is-clickable" action="{{ route('academicsession.set', $session->id) }}" method="post">
                            @csrf
                            <button class="button is-white is-fullwidth has-text-left">{{ $session->session }}</button>
                        </form>
                    @endforeach
                </div>
            </div>
            @endadmin
        </div>

        <div class="navbar-end">
            <div class="navbar-item">
                <div class="buttons">
                    <form method="POST" action="/logout">
                        @csrf
                        <button class="button is-dark">Log Out {{ auth()->user()->full_name }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>
