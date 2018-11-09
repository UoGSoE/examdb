@extends('layouts.app')

@section('content')

<course-viewer
  :course="{{ $course->toJson() }}"
  :papers="{{ $papers->toJson() }}"
  :subcategories='@json(config("exampapers.paper_subcategories"))'
  :user="{{ auth()->user()->toJson() }}"
  :staff="{{ $staff->toJson() }}"
  :externals="{{ $externals->toJson() }}"
>
</course-viewer>

@endsection
