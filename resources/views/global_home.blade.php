@extends('layouts.login')

@section('content')

    <div>Hello</div>
    <div>
        @foreach ($tenants as $tenant)
            <li>
                <a href="http://{{ $tenant->domains->first()->domain }}">
                    {{ $tenant->domains->first()->domain }}
                </a>
            </li>
        @endforeach
    </div>
@endsection
