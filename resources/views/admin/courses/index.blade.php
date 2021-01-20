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
            <a href="{{ route('admin.course.export') }}" class="button">
                <span class="icon"><i class="fas fa-file-download "></i></span>
                <span>Export Excel</span>
            </a>
        </span>
        <span class="level-item">
            <remove-staff-button></remove-staff-button>
        </span>
        <span class="level-item">
            <wlm-importer></wlm-importer>
        </span>
        <span class="level-item">
            <a href="{{ route('course.index', ['withtrashed' => 1, 'discipline' => $disciplineFilter]) }}" class="button">Include Disabled</a>
        </span>
    </div>
</div>
<div class="field is-grouped">
    <p class="control">
        <a class="button" href="{{ route('course.index') }}">
            All
        </a>
    </p>
    @foreach ($disciplines as $discipline)
    <p class="control">
        <a href="{{ route('course.index', ['discipline' => $discipline->id]) }}" class="button @if ($discipline->id == $disciplineFilter) is-info @endif" @if ($discipline->id == $disciplineFilter) disabled @endif
            >
            {{ $discipline->title }}
        </a>
    </p>
    @endforeach
</div>

<table class="table is-striped is-fullwidth">
    <thead>
        <tr>
            <th width="5%">Code</th>
            <th width="5%">Semester</th>
            <th>Title</th>
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
            <td width="5%">
                <a @if (!$course->trashed()) href="{{ route('course.show', $course) }}" @endif>
                    {{ $course->code }}
                    @if ($course->trashed())
                    <span class="tag is-warning">Disabled</span>
                    @endif
                </a>
            </td>
            <td width="5%">
                @livewire('semester-edit-box', ['course' => $course])
            </td>
            <td>{{ $course->title }}</td>
            <td>{{ optional($course->discipline)->title }}</td>
            <td>
                <span class="icon {{ $course->isApprovedByModerator('main') ? 'has-text-info' : 'has-text-grey-light' }}" title="Moderator approved?">
                    <i class="fas fa-user-graduate"></i>
                </span>
                <span class="icon {{ $course->hasExternalChecklist('main') ? 'has-text-info' : 'has-text-grey-light' }}" title="External has filled checklist?">
                    <i class="fas fa-user-secret"></i>
                </span>
            </td>
            <td>
                <span class="icon {{ $course->isApprovedByModerator('resit') ? 'has-text-info' : 'has-text-grey-light' }}" title="Moderator approved?">
                    <i class="fas fa-user-graduate"></i>
                </span>
                <span class="icon {{ $course->hasExternalChecklist('resit') ? 'has-text-info' : 'has-text-grey-light' }}" title="External has filled checklist?">
                    <i class="fas fa-user-secret"></i>
                </span>
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
