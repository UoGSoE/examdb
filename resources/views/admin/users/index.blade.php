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
    <div class="level-right">
        <div class="level-item">
            <a href="{{ route('user.index', ['withtrashed' => true]) }}" class="button">
                Include disabled users
            </a>
        </div>
    </div>
</div>
<user-list :users='@json($users)'></user-list>
@endsection
