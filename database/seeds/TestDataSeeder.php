<?php

use App\Course;
use App\Discipline;
use App\User;
use Illuminate\Database\Seeder;

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
            'email' => 'jenny@example.com',
        ]);

        collect(['Elec', 'MBE', 'Civil', 'UESTC'])->map(function ($title) {
            return Discipline::create(['title' => $title]);
        });
        $courses = factory(Course::class, 10)->create();
        foreach ($courses as $course) {
            $setter->courses()->attach($course->id, ['is_setter' => true]);
            $moderator->courses()->attach($course->id, ['is_moderator' => true]);
            $external->courses()->attach($course->id, ['is_external' => true]);
            $course->discipline()->associate(Discipline::inRandomOrder()->first());
            $course->save();
        }
        $courses = factory(Course::class, 5)->create();
        foreach ($courses as $course) {
            $setter->courses()->attach($course->id, ['is_moderator' => true]);
            $moderator->courses()->attach($course->id, ['is_setter' => true]);
            $external->courses()->attach($course->id, ['is_external' => true]);
            $course->discipline()->associate(Discipline::inRandomOrder()->first());
            $course->save();
        }
    }
}
