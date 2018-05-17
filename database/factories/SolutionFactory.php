<?php

use Faker\Generator as Faker;
use App\User;
use App\Course;

$factory->define(App\Solution::class, function (Faker $faker) {
    return [
        'filename' => $faker->unique()->word . '.pdf',
        'originalFilename' => $faker->unique()->word . '.pdf',
        'mimetype' => 'application/pdf',
        'category' => 'main',
        'size' => $faker->randomNumber(5),
        'user_id' => function () {
            return create(User::class)->id;
        },
        'course_id' => function () {
            return create(Course::class)->id;
        },
    ];
});
