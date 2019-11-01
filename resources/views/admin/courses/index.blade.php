@extends('layouts.app')

@section('content')

<div class="level">
    <div class="level-left">
        <span class="level-item">
            <h3 class="title is-3">
                Course List
            </h3>
        </span>
    </div>
    <div class="level-right">
        <span class="level-item">
            <form method="POST" action="{{ route('admin.courses.clear_staff') }}">
                @csrf
                <button class="button">Clear all staff from courses</button>
            </form>
        </span>
        <span class="level-item">
            <wlm-importer></wlm-importer>
        </span>
        <span class="level-item">
            <a href="{{ route('course.index', ['withtrashed' => 1]) }}" class="button">Include Disabled</a>
        </span>
    </div>
</div>

<table class="table is-striped is-fullwidth">
    <thead>
        <tr>
            <th width="5%">Course</th>
            <th width="7%">Discipline</th>
            <th>Main</th>
            <th>Resit</th>
            <th>Setters</th>
            <th>Moderators</th>
            <th>Externals</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach ($courses as $course)
        <tr>
            <td>
                <a @if (!$course->trashed()) href="{{ route('course.show', $course) }}" @endif>
                    {{ $course->code }}
                    @if ($course->trashed())
                        <span class="tag is-warning">Disabled</span>
                    @endif
                </a>
            </td>
            <td>{{ optional($course->discipline)->title }}</td>
            <td>
                <div class="field has-addons">
                    <p class="control">
                        <span class="icon {{ $course->isApprovedBySetter('main') ? 'has-text-info' : 'has-text-grey-light' }}" title="Setter approved?">
                            <i class="fas fa-user"></i>
                        </span>
                    </p>
                    <p class="control">
                        <span class="icon {{ $course->isApprovedByModerator('main') ? 'has-text-info' : 'has-text-grey-light' }}" title="Moderator approved?">
                            <i class="fas fa-user-graduate"></i>
                        </span>
                    </p>
                    <p class="control">
                        <span class="icon has-text-grey-light" title="External approved?">
                            <i class="fas fa-user-lock"></i>
                        </span>
                    </p>
                </div>
            </td>
            <td>
                <div class="field has-addons">
                    <p class="control">
                        <span class="icon {{ $course->isApprovedBySetter('resit') ? 'has-text-success' : 'has-text-grey-light' }}" title="Setter approved?">
                            <i class="fas fa-user"></i>
                        </span>
                    </p>
                    <p class="control">
                        <span class="icon {{ $course->isApprovedByModerator('resit') ? 'has-text-success' : 'has-text-grey-light' }}" title="Moderator approved?">
                            <i class="fas fa-user-graduate"></i>
                        </span>
                    </p>
                    <p class="control">
                        <span class="icon has-text-grey-light" title="External approved?">
                            <i class="fas fa-user-lock"></i>
                        </span>
                    </p>
                </div>
            </td>
            <td>
                {!! $course->setters->userLinks()->implode(', ') !!}
            </td>
            <td>
                {!! $course->moderators->userLinks()->implode(', ') !!}
            </td>
            <td>
                {!! $course->externals->userLinks()->implode(', ') !!}
            </td>
            <td>
                @if ($course->trashed())
                    <form action="{{ route('course.enable', $course->id) }}" method="POST">
                        @csrf
                        <button class="button is-small">Re-enable</button>
                    </form>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@endsection