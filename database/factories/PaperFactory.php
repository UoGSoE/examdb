<?php

namespace Database\Factories;

use App\Course;
use App\Paper;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaperFactory extends Factory
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
            'original_filename' => $this->faker->unique()->word().'.pdf',
            'mimetype' => 'application/pdf',
            'category' => 'main',
            'subcategory' => 'Pre-Internally Moderated Paper',
            'size' => $this->faker->randomNumber(5),
            'user_id' => function () {
                return create(User::class)->id;
            },
            'course_id' => function () {
                return create(Course::class)->id;
            },
        ];
    }

    public function registry()
    {
        return $this->state(function () {
            return [
                'subcategory' => Paper::PAPER_FOR_REGISTRY,
            ];
        });
    }
}
