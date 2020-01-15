@extends('layouts.app')

@section('content')

<div class="level">
    <div class="level-left">
        <h3 class="title is-3 level-item">
            Exam Paper List
        </h3>
    </div>
    <div class="level-right">
        <export-checklists-button></export-checklists-button>
        <export-papers-registry-button></export-papers-registry-button>
        <a class="button level-item" href="{{ route('area.papers.archive_form') }}">Archive Papers</a>
        <a class="button level-item" href="{{ route('admin.notify.externals.show') }}">Notify Externals</a>
    </div>
</div>

<table class="table is-fullwidth is-striped is-hoverable is-bordered">
    <thead>
        <tr>
            <th>Course</th>
            <th>Pre Internally moderated</th>
            <th>Moderator Comments</th>
            <th>Post Internally moderated</th>
            <th>External Examiner Comments</th>
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
            <td>
                {{ $course->datePaperAdded($category, 'Pre-Internally Moderated Paper') }}
            </td>
            <td>
                {{ $course->datePaperAdded($category, 'Moderator Comments') }}
            </td>
            <td>
                {{ $course->datePaperAdded($category, 'Post-Internally Moderated Paper') }}
            </td>
            <td>
                {{ $course->datePaperAdded($category, 'External Examiner Comments') }}
            </td>
            <td>
                {{ $course->datePaperAdded($category, 'Paper For Registry') }}
            </td>
        </tr>
        @endforeach
        @endforeach
    </tbody>
</table>
@endsection