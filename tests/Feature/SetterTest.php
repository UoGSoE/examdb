<?php

namespace Tests\Feature;

use App\User;
use App\Course;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Paper;
use App\Solution;

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
        $this->withoutExceptionHandling();
        Storage::fake('exampapers');
        $staff = create(User::class);
        $course = create(Course::class);
        $staff->markAsSetter($course);

        $response = $this->actingAs($staff)->post(route('course.paper.store', $course->id), [
            'paper' => UploadedFile::fake()->create('main_paper_1.pdf', 1),
            'category' => 'main',
            'comment' => 'Whatever',
            'comment_category' => 'Something',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('course.show', $course->id));
        $this->assertCount(1, $course->mainPapers);
        $this->assertCount(1, $course->mainPapers->first()->comments);
        Storage::disk('exampapers')->assertExists($course->mainPapers->first()->filename);
    }

    /** @test */
    public function a_user_can_add_a_resit_paper_and_comment_to_a_course()
    {
        $this->withoutExceptionHandling();
        Storage::fake('exampapers');
        $staff = create(User::class);
        $course = create(Course::class);
        $staff->markAsSetter($course);

        $response = $this->actingAs($staff)->post(route('course.paper.store', $course->id), [
            'paper' => UploadedFile::fake()->create('resit_paper_1.pdf', 1),
            'category' => 'resit',
            'comment' => 'Whatever',
            'comment_category' => 'Something',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('course.show', $course->id));
        $this->assertCount(1, $course->resitPapers);
        $this->assertCount(1, $course->resitPapers->first()->comments);
        Storage::disk('exampapers')->assertExists($course->resitPapers->first()->filename);
    }

    /** @test */
    public function a_user_can_add_a_main_solution_and_comment_to_a_course()
    {
        $this->withoutExceptionHandling();
        Storage::fake('exampapers');
        $staff = create(User::class);
        $course = create(Course::class);
        $staff->markAsSetter($course);

        $response = $this->actingAs($staff)->post(route('course.solution.store', $course->id), [
            'solution' => UploadedFile::fake()->create('solution_paper_1.pdf', 1),
            'category' => 'main',
            'comment' => 'Whatever',
            'comment_category' => 'Something',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('course.show', $course->id));
        $this->assertCount(1, $course->mainSolutions);
        $this->assertCount(1, $course->mainSolutions->first()->comments);
        Storage::disk('exampapers')->assertExists($course->mainSolutions->first()->filename);
    }

    /** @test */
    public function a_user_can_add_a_resit_solution_and_comment_to_a_course()
    {
        $this->withoutExceptionHandling();
        Storage::fake('exampapers');
        $staff = create(User::class);
        $course = create(Course::class);
        $staff->markAsSetter($course);

        $response = $this->actingAs($staff)->post(route('course.solution.store', $course->id), [
            'solution' => UploadedFile::fake()->create('solution_paper_1.pdf', 1),
            'category' => 'resit',
            'comment' => 'Whatever',
            'comment_category' => 'Something',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('course.show', $course->id));
        $this->assertCount(1, $course->resitSolutions);
        $this->assertCount(1, $course->resitSolutions->first()->comments);
        Storage::disk('exampapers')->assertExists($course->resitSolutions->first()->filename);
    }

    /** @test */
    public function a_user_can_add_a_comment_to_a_paper_or_solution()
    {
        $this->withoutExceptionHandling();
        $user = create(User::class);
        $paper = create(Paper::class);
        $solution = create(Solution::class, ['course_id' => $paper->course->id]);

        $response = $this->actingAs($user)->post(route('paper.comment', $paper->id), [
            'category' => 'Whatever',
            'comment' => 'MY COMMENT',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('course.show', $paper->course->id));
        $this->assertCount(1, $paper->comments);
        $this->assertEquals('Whatever', $paper->comments->first()->category);
        $this->assertEquals('MY COMMENT', $paper->comments->first()->comment);

        $response = $this->actingAs($user)->post(route('solution.comment', $solution->id), [
            'category' => 'Blah',
            'comment' => 'MY SOLUTION',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('course.show', $solution->course->id));
        $this->assertCount(1, $solution->comments);
        $this->assertEquals('Blah', $solution->comments->first()->category);
        $this->assertEquals('MY SOLUTION', $solution->comments->first()->comment);
    }

    /** @test */
    public function a_user_can_view_all_main_exam_materials_for_a_course()
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
