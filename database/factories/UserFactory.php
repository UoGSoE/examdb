<?php

namespace Database\Factories;

use App\AcademicSession;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
 */

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'surname' => str_replace("'", '', $this->faker->lastName()),
            'forenames' => str_replace("'", '', $this->faker->firstName()),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
            'is_admin' => false,
            'is_staff' => true,
            'is_external' => false,
            'username' => Str::random(3).$this->faker->randomNumber(3).$this->faker->randomLetter(),
            'remember_token' => Str::random(10),
            'academic_session_id' => optional(AcademicSession::first())->id ?? AcademicSession::factory(),
        ];
    }

    public function admin()
    {
        return $this->state(fn ($attr) => ['is_admin' => true]);
    }

    public function external()
    {
        return $this->state(function () {
            $email = $this->faker->unique()->safeEmail();

            return [
                'username' => $email,
                'email' => $email,
                'is_external' => true,
            ];
        });
    }
}
