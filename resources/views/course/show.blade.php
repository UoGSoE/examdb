@extends('layouts.app')

@section('content')

<h1>Details for course {{ $course->full_name }}</h1>

<div class="md:flex">

    <div class="flex:1">
        <h2>Main Exam</h2>
    </div>

    <div class="flex:1">
        Resit Exam
    </div>
</div>

@endsection
