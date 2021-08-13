@extends('layouts.app')

@section('content')

<h3 class="title is-3">Manage Academic Sessions</h3>

@foreach($academicSessions as $academicSession)
    <div class="level">
        <div class="level-left">
            <div class="level-item">
                {{ $academicSession->session }}
            </div>
            <div class="level-item">
                @if ($academicSession->is_default)
                    <span class="tag">Default</span>
                @else
                    <form action="{{ route('academicsession.default.update', $academicSession->id) }}" method="post">
                        @csrf
                        <button class="button is-small">Make Default</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endforeach

<hr>

<form action="{{ route('academicsession.store') }}" method="post">
    @csrf
    <label class="label">Create New Session</label>
    <p class="mb-4"><b>Note:</b> Creating a new session will copy all of the data from the <b><em>current default session ({{ $academicSessions->where('is_default', true)->first()->session }})</em></b> into the new one.</p>
    <div class="field has-addons">
        <div class="control">
          <input class="input" type="text" name="new_session_year_1" value="{{ old('new_session_year_1') }}">
        </div>
        <div class="control">
            <button class="button is-static">/</button>
        </div>
        <div class="control">
            <input class="input" type="text" name="new_session_year_2" value="{{ old('new_session_year_2') }}">
          </div>
      </div>

      @error('new_session_year_1')
          <div class="has-text-danger">{{ $message }}</div>
      @enderror
      @error('new_session_year_2')
          <div class="has-text-danger">{{ $message }}</div>
      @enderror
      @error('session_name')
          <div class="has-text-danger">{{ $message }}</div>
      @enderror

      <div class="field">
          <div class="control">
              <button class="button">Create</button>
          </div>
      </div>
</form>
@endsection
