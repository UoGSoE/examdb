<?php

namespace Database\Seeders;

use App\AcademicSession;
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
        cache()->forget('navbarAcademicSessions');
        $session = AcademicSession::createFirstSession();
        $admin = User::factory()->create([
            'username' => 'admin',
            'password' => bcrypt('secret'),
            'is_admin' => true,
        ]);
        $setter = User::factory()->create([
            'username' => 'setter',
            'password' => bcrypt('secret'),
            'is_admin' => false,
        ]);
        $moderator = User::factory()->create([
            'username' => 'moderator',
            'password' => bcrypt('secret'),
            'is_admin' => false,
        ]);
        User::factory()->count(5)->create();
        User::factory()->count(5)->external()->create();
        $external = User::factory()->external()->create([
            'username' => 'jenny@example.com',
            'email' => 'jenny@example.com',
        ]);

        collect(['Elec', 'BME', 'Civil', 'UESTC'])->map(function ($title) {
            return Discipline::factory()->create(['title' => $title]);
        });
        $courses = Course::factory()->count(10)->create();
        foreach ($courses as $course) {
            $setter->courses()->attach($course->id, ['is_setter' => true]);
            $moderator->courses()->attach($course->id, ['is_moderator' => true]);
            $external->courses()->attach($course->id, ['is_external' => true]);
            $course->discipline()->associate(Discipline::inRandomOrder()->first());
            $course->save();
        }
        $courses = Course::factory()->count(5)->create();
        foreach ($courses as $course) {
            $setter->courses()->attach($course->id, ['is_moderator' => true]);
            $moderator->courses()->attach($course->id, ['is_setter' => true]);
            $external->courses()->attach($course->id, ['is_external' => true]);
            $course->discipline()->associate(Discipline::inRandomOrder()->first());
            $course->save();
        }
        $courses = Course::factory()->count(5)->uestc()->create();
        foreach ($courses as $course) {
            $setter->courses()->attach($course->id, ['is_moderator' => true]);
            $moderator->courses()->attach($course->id, ['is_setter' => true]);
            $external->courses()->attach($course->id, ['is_external' => true]);
            $course->discipline()->associate(Discipline::inRandomOrder()->first());
            $course->save();
        }
    }
}
