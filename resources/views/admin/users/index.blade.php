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

<table class="table is-striped is-fullwidth is-hoverable">
    <thead>
        <tr>
            <th>
                Name
            </th>
            <th>
                Username
            </th>
            <th>
                Email
            </th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach ($users as $user)
            <tr>
                <td>
                    <a href="{{ route('user.show', $user) }}">
                        @if ($user->isExternal())
                            <span class="icon has-text-info" title="External">
                                <i class="fas fa-globe-americas"></i>
                            </span>
                        @endif
                        {{ $user->full_name }}
                        @if ($user->isAdmin())
                            <span class="tag is-dark" title="Admin">
                                Admin
                            </span>
                        @endif
                    </a>
                </td>
                <td>
                    {{ $user->username }}
                </td>
                <td>
                    <a href="mailto:user->email">
                        {{ $user->email }}
                    </a>
                </td>
                <td>
                    <form method="POST" action="{{ route('impersonate.start', $user) }}">
                        @csrf
                        <button class="button is-small">
                            Impersonate
                        </button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection
