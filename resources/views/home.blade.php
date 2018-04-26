@extends('layouts.app')

@section('content')

<h1>Your Courses</h1>

<div class="md:flex">

    <div class="flex:1">
        <h2>Setting</h2>
        @foreach ($setting as $course)
            <li>{{ $course->full_name }}</li>
        @endforeach
    </div>

    <div class="flex:1">
        <h2>Moderating</h2>
    </div>

</div>

@endsection
