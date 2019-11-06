<?php

use Faker\Generator as Faker;
use App\User;
use App\Course;
use App\Paper;

$factory->define(App\Paper::class, function (Faker $faker) {
    return [
        'filename' => $faker->unique()->word . '.pdf',
        'original_filename' => $faker->unique()->word . '.pdf',
        'mimetype' => 'application/pdf',
        'category' => 'main',
        'subcategory' => 'Pre-Internally Moderated Paper',
        'size' => $faker->randomNumber(5),
        'user_id' => function () {
            return create(User::class)->id;
        },
        'course_id' => function () {
            return create(Course::class)->id;
        },
    ];
});

$factory->state(App\Paper::class, 'registry', function (Faker $faker) {
    return [
        'subcategory' => Paper::PAPER_FOR_REGISTRY,
    ];
});
