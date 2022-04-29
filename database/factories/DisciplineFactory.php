<?php

namespace Database\Factories;

use App\AcademicSession;
use App\Discipline;
use Illuminate\Database\Eloquent\Factories\Factory;

class DisciplineFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Discipline::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->word(),
            'academic_session_id' => optional(AcademicSession::first())->id ?? AcademicSession::factory(),
        ];
    }
}
