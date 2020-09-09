@extends('layouts.app')

@section('content')

<div class="level">
    <div class="level-left">
        <div class="level-item">
            <h3 class="title is-3 has-text-grey-dark">
                {{ ucfirst($checklist->category) }} Paper Checklist for <a href="{{ route('course.show', $checklist->course->id) }}" class="has-text-info">{{ $checklist->course->code }}</a>.
            </h3>
        </div>
    </div>
</div>

<p class="subtitle">
    Last edited by {{ $checklist->user->full_name }}
    @if ($checklist->created_at)
    on {{ $checklist->created_at->format('d/m/Y') }} at {{ $checklist->created_at->format('H:i') }}
    @endif
    .
</p>

@livewire('paper-checklist', ['course' => $course, 'category' => $category, 'checklist' => isset($checklist) ? $checklist : null])

@endsection
