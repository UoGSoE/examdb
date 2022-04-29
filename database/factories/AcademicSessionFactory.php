<?php

namespace Database\Factories;

use App\AcademicSession;
use Illuminate\Database\Eloquent\Factories\Factory;

class AcademicSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'session' => now()->year.'/'.(now()->year + 1),
            'is_default' => false,
        ];
    }
}
