<?php

namespace Database\Factories;

use App\Course;
use App\PaperChecklist;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaperChecklistFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PaperChecklist::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
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
