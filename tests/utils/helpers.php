<?php

function create($class, $attributes = [], $times = null)
{
    return $class::factory()->count($times)->create($attributes);
}

function make($class, $attributes = [], $times = null)
{
    return $class::factory()->count($times)->make($attributes);
}
function login($user = null)
{
    if (! $user) {
        $user = \App\Models\User::factory()->create();
    }
    auth()->login($user);

    return $user;
}
