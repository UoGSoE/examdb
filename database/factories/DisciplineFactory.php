<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Discipline;
use Faker\Generator as Faker;

$factory->define(Discipline::class, function (Faker $faker) {
    return [
        'title' => $faker->word,
    ];
});
