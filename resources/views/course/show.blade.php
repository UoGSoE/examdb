@extends('layouts.app')

@section('content')

<h2 class="title is-2 has-text-grey-dark">{{ $course->full_name }}</h2>

<div class="columns">
    <div class="column">
        <h3 class="title has-text-grey">
            Main Exam
            <button class="button">
                <span class="icon has-text-info">
                    <i class="far fa-question-circle"></i>
                </span>
                <span>Add Paper</span>
            </button>
            <button class="button">
                <span class="icon has-text-success">
                    <i class="far fa-check-circle"></i>
                </span>
                <span>Add Solution</span>
            </button>
        </h3>
        <ul>
            @foreach ($course->mainPapers as $paper)
                <li>
                    {{ $paper->filename }}
                    <span class="has-text-grey-light">{{ $paper->created_at->diffForHumans() }}</span>
                </li>
            @endforeach
        </ul>
    </div>
    <div class="column">
        <h3 class="title has-text-grey">Resit Exam</h3>
    </div>
</div>
@endsection
