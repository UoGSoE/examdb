<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Course;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = factory(User::class)->create([
            'username' => 'admin',
            'password' => bcrypt('secret'),
            'is_admin' => true,
        ]);
        $setter = factory(User::class)->create([
            'username' => 'setter',
            'password' => bcrypt('secret'),
            'is_admin' => false,
        ]);
        $moderator = factory(User::class)->create([
            'username' => 'moderator',
            'password' => bcrypt('secret'),
            'is_admin' => false,
        ]);
        factory(User::class, 5)->create();
        factory(User::class, 5)->states('external')->create();
        $external = factory(User::class)->states('external')->create([
            'username' => 'jenny@example.com',
            'email' => 'jenny@example.com'
        ]);

        $courses = factory(Course::class, 10)->create();
        foreach ($courses as $course) {
            $setter->courses()->attach($course->id, ['is_setter' => true]);
            $moderator->courses()->attach($course->id, ['is_moderator' => true]);
            $external->courses()->attach($course->id, ['is_external' => true]);
        }
        $courses = factory(Course::class, 5)->create();
        foreach ($courses as $course) {
            $setter->courses()->attach($course->id, ['is_moderator' => true]);
            $moderator->courses()->attach($course->id, ['is_setter' => true]);
            $external->courses()->attach($course->id, ['is_external' => true]);
        }
    }
}
