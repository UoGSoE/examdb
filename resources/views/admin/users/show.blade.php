@extends('layouts.app')

@section('content')

<div class="level">
    <div class="level-left">
        <span class="level-item">
            <h3 class="title is-3">
                Details for user {{ $user->full_name }}
            </h3>
        </span>
        <a href="{{ route('admin.user.edit', $user->id) }}" class="button level-item" title="Edit User">
            <span class="icon">
            <i class="fas fa-user-edit"></i>
            </span>
        </a>
        <span class="level-item">
            <form method="GET" action="{{ route('impersonate', $user->id) }}">
                @csrf
                <button class="button">
                    Impersonate
                </button>
            </form>
        </span>
        <span class="level-item">
            <a href="{{ route('gdpr.export.user', $user->id) }}" class="button">
                GDPR Export
            </a>
        </span>
    </div>
    <div class="level-right">
        @if ($user->isExternal())
        <div class="level-item">
            <anonymise-user-button :user="{{ $user->toJson() }}"></anonymise-user-button>
        </div>
        @endif
        <div class="level-item">
            <delete-user-button :user="{{ $user->toJson() }}"></delete-user-button>
        </div>
    </div>
</div>

<table class="table">
    <tr>
        <th>Username</th>
        <td>{{ $user->username }}</td>
    </tr>
    <tr>
        <th>Email</th>
        <td>
            <a href="mailto:{{ $user->email }}">
                {{ $user->email }}
            </a>
        </td>
    </tr>
</table>

<hr />

<div class="columns">
    @if ($user->isInternal())
    <div class="column">
        <h4 class="title is-4 has-text-grey">
            Setting
        </h4>
        <ul>
            @foreach ($user->courses as $course)
            @if ($course->pivot->is_setter)
            <li>
                <a href="{{ route('course.show', $course) }}">
                    {{ $course->full_name }}
                </a>
            </li>
            @endif
            @endforeach
        </ul>
    </div>
    <div class="column">
        <h4 class="title is-4 has-text-grey">
            Moderating
        </h4>
        <ul>
            @foreach ($user->courses as $course)
            @if ($course->pivot->is_moderator)
            <li>
                <a href="{{ route('course.show', $course) }}">
                    {{ $course->full_name }}
                </a>
            </li>
            @endif
            @endforeach
        </ul>
    </div>
    @else
    <div class="column">
        <h4 class="title is-4 has-text-grey">
            External
        </h4>
        <ul>
            @foreach ($user->courses as $course)
            @if ($course->pivot->is_external)
            <li>
                <a href="{{ route('course.show', $course) }}">
                    {{ $course->full_name }}
                </a>
            </li>
            @endif
            @endforeach
        </ul>
    </div>
    @endif
</div>

<hr />

<h4 class="title is-4 has-text-grey">
    Activity Logs
</h4>

@include('admin.partials.activity_log', ['logs' => $user->logs])

@endsection
