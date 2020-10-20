@extends('layouts.app')

@section('content')

<div class="columns">
    @if (count($setterCourses) > 0 || count($moderatedCourses) > 0)
    <div class="column">
        <h3 class="title is-3 has-text-grey">
            <span class="icon has-text-grey-lighter"><i class="fas fa-book-open"></i></span>
            <span>&nbsp;Setting</span>
        </h3>
        <ul class="leading-loose">
            @foreach ($setterCourses as $course)
            <li>
                @include('partials.course_status_badge')
                <a href="{{ route('course.show', $course->id) }}">
                    {{ $course->code }} {{ $course->title }}
                </a>
                <span class="tag" title="Semester {{ $course->semester }}">S{{ $course->semester }}</span>
            </li>
            @endforeach
        </ul>
    </div>
    <div class="column">
        <h3 class="title is-3 has-text-grey">
            <span class="icon has-text-grey-lighter"><i class="fas fa-book-open"></i></span>
            <span>&nbsp;Moderating</span>
        </h3>
        <ul class="leading-loose">
            @foreach ($moderatedCourses as $course)
            <li>
                @include('partials.course_status_badge')
                <a href="{{ route('course.show', $course->id) }}">
                    {{ $course->code }} {{ $course->title }}
                </a>
                <span class="tag" title="Semester {{ $course->semester }}">S{{ $course->semester }}</span>
            </li>
            @endforeach
        </ul>
    </div>
    @endif
    @if (count($externalCourses) > 0)
    <div class="column">
        <h3 class="title is-3 has-text-grey">
            <span class="icon has-text-grey-lighter"><i class="fas fa-book-open"></i></span>
            <span>&nbsp;External</span>
        </h3>
        <ul class="leading-loose">
            @foreach ($externalCourses as $course)
            <li>
                @include('partials.course_status_badge')
                <a href="{{ route('course.show', $course->id) }}">
                    {{ $course->code }} {{ $course->title }}
                </a>
                <span class="tag" title="Semester {{ $course->semester }}">S{{ $course->semester }}</span>
                <span class="has-text-grey">
                    Last update {{ $course->updated_at->diffForHumans() }}
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
