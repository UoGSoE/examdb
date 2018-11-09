@extends('layouts.app')

@section('content')

<h3 class="title is-3">Activity Log</h3>

@include('admin.partials.activity_log', ['logs' => $logs])

{{ $logs->links('vendor.pagination.bulma') }}

@endsection
