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
            @foreach ($moderatedCourses as $course)
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
            @foreach ($setterCourses as $course)
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
        <h3 class="title is-3 has-text-grey">
            <span class="icon has-text-grey-lighter"><i class="fas fa-book-open"></i></span>
            <span>&nbsp;External</span>
        </h3>
        <ul>
            @foreach ($externalCourses as $course)
            <li>
                <a href="{{ route('course.show', $course->id) }}">
                    {{ $course->code }} {{ $course->title }}
                </a>
                <span class="has-text-grey">
                    Last update {{ $course->latest_upload ? $course->latest_upload->created_at->diffForHumans() : 'n/a' }}
                </span>
            </li>
            @endforeach
        </ul>
    </div>
    @endif
</div>

<hr />

<h3 class="title is-3 has-text-grey">
    <span class="icon has-text-grey-lighter"><i class="fas fa-book-open"></i></span>
    <span>&nbsp;All of your uploads</span>
</h3>
<ul>
    @foreach ($paperList as $paper)
    <li>
        <strong>
            <a href="{{ route("paper.show", $paper->id) }}">
                <span class="icon">
                    <i class="far fa-file"></i>
                </span>
                {{ $paper->created_at->format('d/m/Y H:i') }}
                {{ $paper->original_filename }}
            </a>
        </strong>
        {{ $paper->category }} / {{ $paper->subcategory }}
        <br />
        <span style="margin-left: 2em">
            <strong>{{ $paper->course->code }}</strong> {{ $paper->course->title }}
        </span>
    </li>
    <br />
    @endforeach
</ul>
@endsection 