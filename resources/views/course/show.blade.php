@extends('layouts.app')

@section('content')

<course-viewer :course="{{ $course->toJson() }}" :subcategories='@json(config("exampapers.paper_subcategories"))'></course-viewer>

@endsection
