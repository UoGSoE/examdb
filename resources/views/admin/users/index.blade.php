@extends('layouts.app')

@section('content')

<div class="level">
    <div class="level-left">
        <span class="level-item">
            <h3 class="title is-3">
                Current Users
            </h3>
        </span>
        <span class="level-item">
            <add-local-user></add-local-user>
        </span>
        <span class="level-item">
            <add-external-user></add-external-user>
        </span>
    </div>
</div>
<user-list :users='@json($users)'></user-list>
@endsection
