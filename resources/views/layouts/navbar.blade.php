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

      <div class="navbar-item has-dropdown is-hoverable">
        <a class="navbar-link">
          Admin
        </a>

        <div class="navbar-dropdown">
          <a class="navbar-item" href="{{ route('activity.index') }}">
            Logs
          </a>
          <a class="navbar-item">
            Users
          </a>
          <a class="navbar-item">
            Courses
          </a>
          <hr class="navbar-divider">
          <a class="navbar-item">
            Archives
          </a>
        </div>
      </div>
    </div>

    <div class="navbar-end">
      <div class="navbar-item">
        <div class="buttons">
            <form method="POST" action="/logout">
                @csrf
                <button class="button">Log Out</button>
            </form>
        </div>
      </div>
    </div>
  </div>
</nav>