@extends('layouts.app')

@section('content')

<form method="POST" action="{{ route('admin.notify.externals') }}">
    @csrf
    <div class="field">
        <div class="control">
            <label for="area" class="label">Choose which group of externals to notify</label>
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
    <button class="button">Alert externals about papers</button>
</form>

@endsection