@extends('layouts.app')

@section('content')

<h3 class="title is-3">Previous Papers for <a href="{{ route('course.show', $course->id) }}">{{ $course->code }}</a></h3>

@if($papers->count() == 0)
<h4 class="title is-4 has-text-grey">No previous papers found...</h4>
@else
<table class="table is-fullwidth is-striped">
    <thead>
        <tr>
            <th width="15%">Date</th>
            <th width="15%">Uploaded by</th>
            <th>File</th>
        </tr>
    </thead>
    <tbody>
        @foreach($papers as $paper)
            <tr>
                <td>{{ $paper->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $paper->user->full_name }}</td>
                <td>
                    [<span class="has-text-weight-semibold">{{ ucfirst($paper->category) }}</span>]
                    <a href="{{ route('paper.show', $paper->id) }}" class="has-text-weight-semibold">{{ $paper->original_filename }}</a>
                    @if ($paper->comments->count() > 0)
                    <br />
                    <span class="icon">
                        <i class="fas fa-quote-left has-text-grey-light"></i>
                    </span>
                    {{ $paper->comments->first()?->comment }}
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endif

@endsection
