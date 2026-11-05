<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

#[UseModel(\App\Solution::class)]
class SolutionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'filename' => $this->faker->unique()->word().'.pdf',
            'originalFilename' => $this->faker->unique()->word().'.pdf',
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
