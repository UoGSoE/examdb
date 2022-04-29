<?php

namespace Tests\Feature;

use App\AcademicSession;
use App\Course;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class SemesterEditingTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        AcademicSession::createFirstSession();
    }

    /** @test */
    public function admins_can_see_the_semester_edit_box_on_the_course_index_page()
    {
        $course = Course::factory()->create();
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('course.index'));

        $response->assertOk();
        $response->assertSeeLivewire('semester-edit-box');
    }

    /** @test */
    public function admins_can_change_the_semester_for_a_course()
    {
        $course = Course::factory()->create(['semester' => 1]);
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)->test('semester-edit-box', ['course' => $course])
            ->set('course.semester', 2);

        $this->assertEquals(2, $course->fresh()->semester);
    }

    /** @test */
    public function the_semester_has_to_be_a_number_between_one_and_three()
    {
        $course = Course::factory()->create(['semester' => 1]);
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)->test('semester-edit-box', ['course' => $course])
            ->set('course.semester', 0)
            ->assertHasErrors('course.semester')
            ->set('course.semester', 4)
            ->assertHasErrors('course.semester')
            ->set('course.semester', 'hello')
            ->assertHasErrors('course.semester');

        $this->assertEquals(1, $course->fresh()->semester);
    }
}
