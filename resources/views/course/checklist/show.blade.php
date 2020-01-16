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
    <div class="level-right">
        <div class="level-item">
            @if ($checklist->course->hasPreviousChecklists($checklist, $checklist->category))
            <a href="{{ route('course.checklist.show', $checklist->getPreviousChecklist()) }}" class="button" title="See previous version" aria-label="See previous version">
                <span class="icon">
                    <i class="fas fa-backward"></i>
                </span>
                <span>Previous</span>
            </a>
            @endif
            @if ($checklist->course->hasMoreChecklists($checklist, $checklist->category))
            <a href="{{ route('course.checklist.show', $checklist->getNextChecklist()) }}" class="button" title="See previous version" aria-label="See previous version">
                <span>Next</span>
                <span class="icon">
                    <i class="fas fa-forward"></i>
                </span>
            </a>
            @endif
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
<form disabled>
    <div class="columns">
        <div class="column">
            <h4 class="title is-4 has-text-grey-dark">Setter</h4>
            <div class="field">
                <label class="label has-text-grey" for="q1">Question 1</label>
                <div class="control">
                    <textarea class="textarea" id="q1" name="q1">{{ $checklist->q1 }}</textarea>
                </div>
            </div>

            <div class="field">
                <label class="label has-text-grey" for="q2">Question 2</label>
                <div class="control">
                    <textarea class="textarea" id="q2" name="q2">{{ $checklist->q2 }}</textarea>
                </div>
            </div>
        </div>
        <div class="column">
            <h4 class="title is-4 has-text-grey-dark">Moderator</h4>
            <div class="field">
                <label class="label has-text-grey" for="q1">Question 1</label>
                <div class="control">
                    <textarea class="textarea" id="q1" name="blahq1">{{ $checklist->q1 }}</textarea>
                </div>
            </div>

            <div class="field">
                <label class="label has-text-grey" for="q2">Question 2</label>
                <div class="control">
                    <textarea class="textarea" id="q2" name="blahq2">{{ $checklist->q2 }}</textarea>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection