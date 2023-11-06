<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\PaperChecklist;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaperChecklistFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $fields = make(Course::class)->getDefaultChecklistFields();

        return [
            'version' => PaperChecklist::CURRENT_VERSION,
            'user_id' => function () {
                return User::factory()->create()->id;
            },
            'course_id' => function () {
                return Course::factory()->create()->id;
            },
            'category' => 'main',
            'fields' => $fields,
        ];
    }
}
