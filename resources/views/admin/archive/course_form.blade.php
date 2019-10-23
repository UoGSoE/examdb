@extends('layouts.app')

@section('content')

<form method="POST" action="{{ route('course.papers.archive', $course->id) }}">
    @csrf
    <button class="button">Archive Papers</button>
</form>

@endsection