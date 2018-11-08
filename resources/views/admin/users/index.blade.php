@extends('layouts.app')

@section('content')

<h3 class="title is-3">
    Current Users
</h3>

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
        </tr>
    </thead>
    <tbody>
        @foreach ($users as $user)
            <tr>
                <td>
                    @if ($user->isExternal())
                        <span class="icon has-text-info" title="External">
                            <i class="fas fa-globe-americas"></i>
                        </span>
                    @endif
                    {{ $user->full_name }}
                </td>
                <td>
                    {{ $user->username }}
                </td>
                <td>
                    <a href="mailto:user->email">
                        {{ $user->email }}
                    </a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection
