<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Course;
use App\PaperChecklist;
use App\User;
use Faker\Generator as Faker;

$factory->define(PaperChecklist::class, function (Faker $faker) {
    return [
        'version' => PaperChecklist::CURRENT_VERSION,
        'user_id' => function () {
            return factory(User::class)->create()->id;
        },
        'course_id' => function () {
            return factory(Course::class)->create()->id;
        },
        'category' => 'main',
        'fields' => [],
    ];
});
