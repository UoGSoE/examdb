<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Course;
use App\User;

class SolutionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Solution::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'filename' => $this->faker->unique()->word.'.pdf',
            'originalFilename' => $this->faker->unique()->word.'.pdf',
            'mimetype' => 'application/pdf',
            'category' => 'main',
            'size' => $this->faker->randomNumber(5),
            'user_id' => function () {
                return create(User::class)->id;
            },
            'course_id' => function () {
                return create(Course::class)->id;
            },
        ];
    }
}
