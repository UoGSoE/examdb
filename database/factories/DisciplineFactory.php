<?php

namespace Database\Factories;

use App\Models\AcademicSession;
use Illuminate\Database\Eloquent\Factories\Factory;

class DisciplineFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->word(),
            'academic_session_id' => optional(AcademicSession::first())->id ?? AcademicSession::factory(),
        ];
    }
}
