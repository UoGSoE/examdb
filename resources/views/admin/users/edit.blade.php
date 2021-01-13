@extends('layouts.app')

@section('content')

<div class="level">
    <div class="level-left">
        <span class="level-item">
            <h3 class="title is-3">
                Edit User {{ $user->full_name }}
            </h3>
        </span>
    </div>
</div>

<form action="{{ route('admin.user.update', $user->id) }}" method="post">
    @csrf
    <div class="field">
        <label class="label" for="email">Email</label>
        <div class="control">
            <input class="input" id="email" name="email" type="text" value="{{ old('email', $user->email) }}">
        </div>
    </div>
    @error('email')
        <p class="has-text-danger">{{ $message }}</p>
    @enderror

    <div class="field">
        <label class="label" for="surname">Surname</label>
        <div class="control">
            <input class="input" id="surname" name="surname" type="text" value="{{ old('surname', $user->surname) }}">
        </div>
    </div>
    @error('surname')
        <p class="has-text-danger">{{ $message }}</p>
    @enderror

    <div class="field">
        <label class="label" for="forenames">Forename(s)</label>
        <div class="control">
            <input class="input" id="forenames" name="forenames" type="text" value="{{ old('forenames', $user->forenames) }}">
        </div>
    </div>
    @error('forenames')
        <p class="has-text-danger">{{ $message }}</p>
    @enderror

    <div class="control">
        <button class="button">Update</button>
    </div>
</form>

@endsection
