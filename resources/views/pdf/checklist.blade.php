@extends('layouts.app')

@section('content')

<h3 class="title is-3">Timeline for {{ $checklist->course->code }}</h3>
<div class="box">
    @if($checklist->course->papers()->count() === 0)
        No papers uploaded for this course
    @else
    @foreach ($checklist->course->papers()->orderBy('created_at')->get() as $paper)
        <div class="level">
            <div class="level-left">
                <div class="level-item">
                    <span class="icon">
                        <i class="far fa-calendar-alt"></i>
                    </span>
                    <span class="has-text-weight-medium">{{ $paper->created_at->format('d/m/Y H:i') }}</span>
                </div>
                    <div class="level-item">
                        <span class="has-text-weight-semibold">{{ $paper->user->full_name }}&nbsp;</span>
                        @if ($paper->subcategory != \App\Models\Paper::COMMENT_SUBCATEGORY) uploaded @else added @endif a
                        <span class="has-text-weight-semibold">&nbsp;{{ $paper->subcategory }}</span>
                    </div>
            </div>
        </div>
        @if ($paper->comments->count() > 0)
            <div class="mb-4">
                <span class="pl-4">
                    <span class="icon is-small">
                        <i class="far fa-comment"></i>
                    </span>
                    &nbsp;
                    <span style="overflow-wrap: break-word !important;">
                        {{ $paper->comments->first()->comment }}
                    </span>
                </span>
            </div>
        @endif

    @endforeach
    @endif
</div>
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
