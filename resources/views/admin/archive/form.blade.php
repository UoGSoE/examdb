@extends('layouts.app')

@section('content')

<form method="POST" action="{{ route('area.papers.archive') }}">
    @csrf
    <div class="field">
        <div class="control">
            <label for="area" class="label">Choose which area to archive</label>
            <label class="radio">
                <input type="radio" name="area" value="glasgow" required>
                Glasgow
            </label>
            <label class="radio">
                <input type="radio" name="area" value="uestc" required>
                UESTC
            </label>
        </div>
    </div>
    <hr />
    <button class="button">Archive Papers</button>
</form>

@endsection