<?php

namespace Database\Factories;

use App\Models\AcademicSession;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
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
            'is_examined' => true,
            'academic_session_id' => optional(AcademicSession::first())->id ?? AcademicSession::factory(),
        ];
    }

    public function uestc()
    {
        return $this->state(fn ($attribs) => ['code' => 'UESTC'.$this->faker->numberBetween(1000, 8999)]);
    }

    public function notExamined()
    {
        return $this->state(fn ($attribs) => ['is_examined' => false]);
    }
}
