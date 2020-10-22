<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Course::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'code' => 'ENG'.$this->faker->numberBetween(1000, 8999),
            'title' => $this->faker->text(50),
            'moderator_approved_main' => false,
            'moderator_approved_resit' => false,
            'external_approved_main' => false,
            'external_approved_resit' => false,
            'registry_approved_main' => false,
            'registry_approved_resit' => false,
            'semester' => 1,
        ];
    }

    public function uestc()
    {
        return $this->state(fn ($attribs) => ['code' => 'UESTC'.$this->faker->numberBetween(1000, 8999)]);
    }
}
