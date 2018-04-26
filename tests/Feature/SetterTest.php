<?php

namespace Tests\Feature;

use App\User;
use App\Course;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SetterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_see_all_the_courses_they_are_a_setter_for()
    {
        $staff = create(User::class);
        $course1 = create(Course::class);
        $course2 = create(Course::class);
        $course3 = create(Course::class);
        $staff->markAsSetter($course1);
        $staff->markAsSetter($course2);

        $response = $this->actingAs($staff)->get(route('home'));

        $response->assertSuccessful();
        $this->assertCount(2, $response->data('setting'));
        $this->assertTrue($response->data('setting')->contains($course1));
        $this->assertTrue($response->data('setting')->contains($course2));
        $this->assertFalse($response->data('setting')->contains($course3));
        $response->assertSee($course1->code);
        $response->assertSee($course2->code);
        $response->assertDontSee($course3->code);
    }

    /** @test */
    public function a_user_can_see_the_page_for_an_individual_course_they_are_setter_for()
    {
        $this->withoutExceptionHandling();
        $staff = create(User::class);
        $course1 = create(Course::class);
        $staff->markAsSetter($course1);

        $response = $this->actingAs($staff)->get(route('course.show', $course1->id));

        $response->assertSuccessful();
        $this->assertTrue($response->data('course')->is($course1));
        $response->assertSee('Details for course');
        $response->assertSee($course1->full_name);
        $response->assertSee('Main Exam');
        $response->assertSee('Resit Exam');
    }

    /** @test */
    public function a_user_cant_see_the_page_for_a_course_they_arent_involved_with()
    {
        $staff = create(User::class);
        $course1 = create(Course::class);

        $response = $this->actingAs($staff)->get(route('course.show', $course1->id));

        $response->assertStatus(403);
    }

    /** @test */
    public function a_user_can_add_a_main_paper_and_comment_to_a_course()
    {
        
    }

    /** @test */
    public function a_user_can_add_a_main_solution_and_comment_to_a_course()
    {
        
    }

    /** @test */
    public function a_user_can_view_all_main_exam_materials_for_a_course()
    {
        
    }

    /** @test */
    public function a_user_can_add_a_resit_paper_and_comment_to_a_course()
    {
        
    }

    /** @test */
    public function a_user_can_add_a_resit_solution_and_comment_to_a_course()
    {
        
    }

    /** @test */
    public function a_user_can_view_all_resit_exam_materials_for_a_course()
    {
        
    }

    /** @test */
    public function a_setter_can_approve_or_unapprove_a_main_paper()
    {
        
    }

    /** @test */
    public function an_admin_can_do_anything_regarding_setting()
    {
        
    }
}
