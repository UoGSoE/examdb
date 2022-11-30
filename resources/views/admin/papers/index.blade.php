@extends('layouts.app')

@section('content')

<div class="level">
    <div class="level-left">
        <h3 class="title is-3 level-item">
            Exam Paper List
        </h3>
    </div>
    <div class="level-right">
        <a class="button level-item" href="{{ route('admin.paper.export') }}">
            <span class="icon"><i class="fas fa-file-download"></i></span>
            <span>Export Excel</span>
        </a>
        <export-checklists-button></export-checklists-button>
        <export-papers-registry-button></export-papers-registry-button>
        <a class="button level-item" href="{{ route('admin.notify.externals.show') }}">Notify Externals</a>
    </div>
</div>

<div class="field is-grouped">
    <p class="control">
        <a class="button" href="{{ route('paper.index') }}">
            All
        </a>
    </p>
    @foreach ($disciplines as $discipline)
    <p class="control">
        <a href="{{ route('paper.index', ['discipline' => $discipline->id]) }}" class="button @if ($discipline->id == $disciplineFilter) is-info @endif" @if ($discipline->id == $disciplineFilter) disabled @endif
            >
            {{ $discipline->title }}
        </a>
    </p>
    @endforeach
</div>

<table class="table is-fullwidth is-striped is-hoverable is-bordered">
    <thead>
        <tr>
            <th width="15%">Code</th>
            <th>Semester</th>
            <th>Title</th>
            <th>Discipline</th>
            <th>Check Lists</th>
            <th>Pre Internally moderated</th>
            <th>Post Internally moderated</th>
            <th>Final Paper for Registry</th>
        </tr>
    </thead>
    <tbody>
        @foreach($courses as $course)
        @foreach(['main', 'resit'] as $category)
        <tr>
            <td>
                {{ $course->code }} <span class="tag">{{ $category }}</span>
            </td>
            <td>{{ $course->semester }}</td>
            <td>{{ $course->title }}</td>
            <td>
                {{ $course->discipline?->title }}
            </td>
            <td>
                <span class="icon {{ $course->hasSetterChecklist($category) ? 'has-text-info' : 'has-text-grey-light' }}" title="Setter Checklist">
                    <i class="fas fa-user-tie"></i>
                </span>
                <span class="icon {{ $course->hasModeratorChecklist($category) ? 'has-text-info' : 'has-text-grey-light' }}" title="Moderator Checklist">
                    <i class="fas fa-user-graduate"></i>
                </span>
            </td>
            <td>
                {{ $course->datePaperAdded($category, \App\Models\Paper::PRE_INTERNALLY_MODERATED) }}
            </td>
            <td>
                {{ $course->datePaperAdded($category, \App\Models\Paper::POST_INTERNALLY_MODERATED) }}
            </td>
            <td>
                {{ $course->datePaperAdded($category, \App\Models\Paper::PAPER_FOR_REGISTRY) }}
            </td>
            <td id="print-ready-date">
                {{ $course->datePaperAdded($category, \App\Models\Paper::ADMIN_PRINT_READY_VERSION) }}
            </td>
            <td id="print-ready-status">
            @if ($course->printReadyPaperRejected($category))
                <span class="has-text-danger" title="Rejected">
                    Rejected:
                </span>
                {{ $course->printReadyPaperRejectedMessage($category) }}
            @else
                {{ $course->printReadyPaperApproved($category) ? 'Yes' : 'No' }}
            @endif
            </td>
        </tr>
        @endforeach
        @endforeach
    </tbody>
</table>
@endsection
