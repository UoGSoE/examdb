<?php

use Faker\Generator as Faker;

$factory->define(App\Course::class, function (Faker $faker) {
    return [
        'code' => 'ENG' . $faker->numberBetween(1000, 5999),
        'title' => $faker->text(50),
    ];
});
