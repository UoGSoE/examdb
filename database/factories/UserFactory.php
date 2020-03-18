<?php

use Faker\Generator as Faker;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
 */

$factory->define(App\User::class, function (Faker $faker) {
    return [
        'surname' => str_replace("'", '', $faker->lastName),
        'forenames' => str_replace("'", '', $faker->firstName),
        'email' => $faker->unique()->safeEmail,
        'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
        'is_staff' => true,
        'is_external' => false,
        'username' => Str::random(3).$faker->randomNumber(3).$faker->randomLetter,
        'remember_token' => Str::random(10),
    ];
});

$factory->state(App\User::class, 'external', function (Faker $faker) {
    $email = $faker->unique()->safeEmail;

    return [
        'username' => $email,
        'email' => $email,
        'is_external' => true,
    ];
});
