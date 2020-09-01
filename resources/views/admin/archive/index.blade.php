@extends('layouts.app')

@section('content')

<h3 class="title is-3">Archived Papers</h3>

<table class="table is-striped is-fullwidth">
    <thead>
        <tr>
            <th>Course</th>
            <th>Paper</th>
            <th>User</th>
            <th>Uploaded</th>
            <th>Archived</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($papers as $paper)
        <tr>
            <td>{{ $paper->course->code }}</td>
            <td>
                @if ($paper->subcategory == \App\Paper::PAPER_FOR_REGISTRY)
                <a href="{{ route('archived.paper.show', $paper->id) }}">
                    {{ ucfirst($paper->category) }} {{ $paper->subcategory }}
                </a>
                @else
                {{ ucfirst($paper->category) }} {{ $paper->subcategory }}
                @endif
            </td>
            <td>{{ $paper->user->full_name }}</td>
            <td>{{ $paper->created_at->format('d/m/Y H:i') }}</td>
            <td>{{ $paper->archived_at->format('d/m/Y H:i') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<h3 class="title is-3">Archived Checklists</h3>

<table class="table is-striped is-fullwidth">
    <thead>
        <tr>
            <th>Course</th>
            <th>Category</th>
            <th>Last Updated</th>
            <th>Archived</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($checklists as $checklist)
        <tr>
            <td>{{ $checklist->course->code }}</td>
            <td>
                {{ ucfirst($checklist->category) }}
            </td>
            <td>{{ $checklist->created_at->format('d/m/Y H:i') }}</td>
            <td>{{ $checklist->archived_at->format('d/m/Y H:i') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

@endsection
