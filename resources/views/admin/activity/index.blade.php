@extends('layouts.app')

@section('content')

<h3 class="title is-3">Activity Log</h3>

<table class="table is-striped is-fullwidth">
    <thead>
        <tr>
            <th>Date</th>
            <th>User</th>
            <th>Event</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($logs as $log)
            <tr>
                <td>
                    {{ $log->created_at->format('d/m/Y H:i') }}
                </td>
                <td>
                    {{ optional($log->causer)->full_name }}
                </td>
                <td>
                    {{ $log->description }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection
