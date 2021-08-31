@extends('layouts.app')

@section('content')
<course-viewer :course="{{ $course->toJson() }}" :papers="{{ $papers->toJson() }}" :subcategories='@json(config("exampapers.paper_subcategories"))' :user="{{ auth()->user()->toJson() }}" :staff="{{ $staff->toJson() }}" :externals="{{ $externals->toJson() }}">
</course-viewer>

@endsection

@push('scripts')
<script>
  window.is_moderator = @json(Auth::check() && Auth::user()->isModeratorFor($course));
  window.api_key = '{{ config('exampapers.api_key') }}';
</script>
@endpush
