@extends('layouts.app')

@section('content')

<form action="{{ route('course.update', $course->id) }}" method="post">
    @csrf
    <div class="field">
        <label class="label" for="code">Code</label>
        <div class="control">
            <input class="input" id="code" name="code" type="text" value="{{ old('code', $course->code) }}">
        </div>
    </div>
    @error('code')
        <p class="has-text-danger">{{ $message }}</p>
    @enderror
    <div class="field">
        <label class="label" for="title">Title</label>
        <div class="control">
            <input class="input" id="title" name="title" type="text" value="{{ old('title', $course->title) }}">
        </div>
    </div>
    @error('title')
        <p class="has-text-danger">{{ $message }}</p>
    @enderror

    <div class="field">
        <label class="label" for="discipline_id">Discipline</label>
        <div class="control">
            <div class="select">
            <select id="discipline_id" name="discipline_id">
                @foreach ($disciplines as $discipline)
                    <option value="{{ $discipline->id }}" @if (old("discipline_id", $discipline->id) == $course->discipline_id) selected @endif>{{ $discipline->title }}</option>
                @endforeach
            </select>
            </div>
        </div>
    </div>
    @error('title')
        <p class="has-text-danger">{{ $message }}</p>
    @enderror

    <div class="field">
        <div class="control">
            <button class="button">Save</button>
        </div>
    </div>
</form>
@endsection
