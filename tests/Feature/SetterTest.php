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
        $this->assertCount(2, $response->data('setterCourses'));
        $this->assertTrue($response->data('setterCourses')->contains($course1));
        $this->assertTrue($response->data('setterCourses')->contains($course2));
        $this->assertFalse($response->data('setterCourses')->contains($course3));
        $this->assertCount(0, $response->data('moderatedCourses'));
        $this->assertCount(0, $response->data('externalCourses'));
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
        $mainPaper = create(Paper::class, ['course_id' => $course1->id, 'category' => 'main']);
        $resitPaper = create(Paper::class, ['course_id' => $course1->id, 'category' => 'resit']);
        $mainSolution = create(Solution::class, ['course_id' => $course1->id, 'category' => 'main']);
        $resitSolution = create(Solution::class, ['course_id' => $course1->id, 'category' => 'resit']);

        $response = $this->actingAs($staff)->get(route('course.show', $course1->id));

        $response->assertSuccessful();
        $this->assertTrue($response->data('course')->is($course1));
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

        $response = $this->actingAs($staff)->postJson(route('course.paper.store', $course->id), [
            'paper' => UploadedFile::fake()->create('main_paper_1.pdf', 1),
            'category' => 'main',
            'subcategory' => 'fred',
            'comment' => 'Whatever',
        ]);

        $response->assertStatus(201);
        $this->assertCount(1, $course->papers);
        $this->assertCount(1, $course->papers->first()->comments);
        Storage::disk('exampapers')->assertExists($course->papers->first()->filename);
        tap($course->papers->first(), function ($paper) use ($staff, $course) {
            $this->assertEquals('main', $paper->category);
            $this->assertEquals('fred', $paper->subcategory);
            $this->assertEquals('Whatever', $paper->comments->first()->comment);
            $this->assertTrue($paper->user->is($staff));
            $this->assertTrue($paper->course->is($course));
        });
    }

    /** @test */
    public function a_user_can_add_a_resit_paper_and_comment_to_a_course()
    {
        $this->withoutExceptionHandling();
        Storage::fake('exampapers');
        $staff = create(User::class);
        $course = create(Course::class);
        $staff->markAsSetter($course);

        $response = $this->actingAs($staff)->postJson(route('course.paper.store', $course->id), [
            'paper' => UploadedFile::fake()->create('main_paper_1.pdf', 1),
            'category' => 'resit',
            'subcategory' => 'fred',
            'comment' => 'Whatever',
        ]);

        $response->assertStatus(201);
        $this->assertCount(1, $course->papers);
        $this->assertCount(1, $course->papers->first()->comments);
        Storage::disk('exampapers')->assertExists($course->papers->first()->filename);
        tap($course->papers->first(), function ($paper) use ($staff, $course) {
            $this->assertEquals('resit', $paper->category);
            $this->assertEquals('fred', $paper->subcategory);
            $this->assertEquals('Whatever', $paper->comments->first()->comment);
            $this->assertTrue($paper->user->is($staff));
            $this->assertTrue($paper->course->is($course));
        });
    }

    /** @test */
    public function a_setter_can_approve_a_paper()
    {
        $user = create(User::class);
        $paper = create(Paper::class);
        $this->assertFalse($paper->fresh()->isApprovedBySetter());

        $response = $this->actingAs($user)->post(route('paper.approve', $paper));

        $response->assertStatus(302);
        $response->assertRedirect(route('course.show', $paper->course->id));
        $this->assertTrue($paper->fresh()->isApprovedBySetter());
    }

    /** @test */
    public function a_setter_can_unapprove_a_paper()
    {
        $user = create(User::class);
        $paper = create(Paper::class, ['approved_setter' => true]);
        $this->assertTrue($paper->fresh()->isApprovedBySetter());

        $response = $this->actingAs($user)->post(route('paper.unapprove', $paper));

        $response->assertStatus(302);
        $response->assertRedirect(route('course.show', $paper->course->id));
        $this->assertFalse($paper->fresh()->isApprovedBySetter());
    }

    /** @test */
    public function an_admin_can_do_anything_regarding_setting()
    {

    }
}
