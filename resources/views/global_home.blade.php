@extends('layouts.login')

@section('content')
<div class="loginbox">
    <div class="columns is-centered">

        <div class="column is-one-third">

            <div class="shadow-lg login-form">
                <div class="login-header">
                    <h1 class="title is-1">ExamDB</h1>
                </div>
                <article key="1" style="background: hsl(0, 0%, 100%); color: hsl(0, 0%, 21%); text-align: center;" class="p-8">
                    Please choose which school to log into.
                </article>

                <div key="2" method="POST" action="/login" class=" p-8 ">
                    <ul>
                    @foreach ($tenants as $tenant)
                        <li>
                            <a href="http://{{ $tenant->domains->first()->domain }}" class="button is-info is-fullwidth has-text-weight-bold">
                                {{ $tenant->domains->first()->domain }}
                            </a>
                            <br>
                        </li>
                    @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div><!-- loginbox -->
@endsection
