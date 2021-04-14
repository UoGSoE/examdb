<?php

namespace Database\Factories;

use App\Sysadmin;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class SysadminFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Sysadmin::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'surname' => str_replace("'", '', $this->faker->lastName),
            'forenames' => str_replace("'", '', $this->faker->firstName),
            'email' => $this->faker->unique()->safeEmail,
            'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
            'is_staff' => true,
            'is_sysadmin' => true,
            'username' => Str::random(3).$this->faker->randomNumber(3).$this->faker->randomLetter,
            'remember_token' => Str::random(10),
        ];
    }
}
