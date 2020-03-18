<?php

function create($class, $attributes = [], $times = null)
{
    return factory($class, $times)->create($attributes);
}
function make($class, $attributes = [], $times = null)
{
    return factory($class, $times)->make($attributes);
}
function login($user = null)
{
    if (! $user) {
        $user = factory(\App\User::class)->create();
    }
    auth()->login($user);

    return $user;
}
