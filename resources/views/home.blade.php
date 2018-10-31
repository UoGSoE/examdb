@extends('layouts.app')

@section('content')

<div class="columns">
    @if (auth()->user()->isInternal())
        <div class="column">
            <h3 class="title is-3 has-text-grey">
                <span class="icon has-text-grey-lighter"><i class="fas fa-book-open"></i></span>
                <span>&nbsp;Moderating</span>
            </h3>
            <ul>
                @foreach (auth()->user()->courses()->wherePivot('is_moderator', true)->get() as $course)
                    <li>
                        <a href="{{ route('course.show', $course->id) }}">
                            {{ $course->code }} {{ $course->title }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="column">
            <h3 class="title is-3 has-text-grey">
                <span class="icon has-text-grey-lighter"><i class="fas fa-book-open"></i></span>
                <span>&nbsp;Setting</span>
            </h3>
            <ul>
                @foreach (auth()->user()->courses()->wherePivot('is_setter', true)->get() as $course)
                    <li>
                        <a href="{{ route('course.show', $course->id) }}">
                            {{ $course->code }} {{ $course->title }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @else
        <div class="column">
            <h3 class="title is-3 has-text-grey">External</h3>
        </div>
    @endif
</div>

@endsection
