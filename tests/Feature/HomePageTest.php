<?php

namespace Tests\Feature;

use App\AcademicSession;
use App\Course;
use App\Paper;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        AcademicSession::createFirstSession();
    }

    /** @test */
    public function users_see_courses_they_are_setting_moderator_external_and_all_papers_they_have_uploaded()
    {
        $staff = create(User::class);
        $course1 = create(Course::class);
        $course2 = create(Course::class);
        $course3 = create(Course::class);
        $course4 = create(Course::class);
        $staff->markAsSetter($course1);
        $staff->markAsModerator($course2);
        $staff->markAsExternal($course3);
        $oldPaper1 = create(Paper::class, ['user_id' => $staff->id]);
        $oldPaper2 = create(Paper::class, ['user_id' => $staff->id]);
        $oldPaper3 = create(Paper::class);

        $response = $this->actingAs($staff)->get(route('home'));

        $response->assertSuccessful();
        $this->assertCount(1, $response->data('setterCourses'));
        $this->assertTrue($response->data('setterCourses')->contains($course1));
        $this->assertCount(1, $response->data('moderatedCourses'));
        $this->assertTrue($response->data('moderatedCourses')->contains($course2));
        $this->assertCount(1, $response->data('externalCourses'));
        $this->assertTrue($response->data('externalCourses')->contains($course3));
        $response->assertDontSee($course4->code);
        $this->assertCount(2, $response->data('paperList'));
        $this->assertTrue($response->data('paperList')->contains($oldPaper1));
        $this->assertTrue($response->data('paperList')->contains($oldPaper2));
        $this->assertFalse($response->data('paperList')->contains($oldPaper3));
    }
}
