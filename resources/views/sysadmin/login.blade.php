@extends('layouts.login')

@section('content')
    <div class="loginbox">
        <div class="columns is-centered">

            <div class="column is-one-third">
                <div class="shadow-lg login-form">
                    <div class="login-header" style="background-color: hsl(348, 100%, 61%);">
                        <h1 class="title is-1">ExamDB Login</h1>
                    </div>

                    @error('auth')
                        <div class="notification is-warning p-8">
                            {{ $message }}
                        </div>
                    @enderror

                    <form key="2" method="POST" action="/login" class="p-8">
                        @csrf
                        <div class="field">
                            <label class="label">Username <span class="has-text-grey has-text-weight-light">(GUID)</span></label>
                            <p class="control">
                                <input class="input" type="text" name="username" autofocus>
                            </p>
                        </div>
                            <div class="field">
                                <label class="label">Password</label>
                                <p class="control">
                                    <input class="input" type="password" name="password">
                                </p>
                            </div>
                        <hr />
                        <div class="field">
                            <button class="button is-info is-fullwidth">Log In</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div><!-- loginbox -->

@endsection
