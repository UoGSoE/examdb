@extends('layouts.app')

@section('content')

<div class="columns">
    <div class="column">
        <options-editor :options='@json($options)'></options-editor>
    </div>
    <div class="column">
        <discipline-contacts-editor :disciplines='@json($disciplines)'></discipline-contacts-editor>
    </div>
</div>


@endsection