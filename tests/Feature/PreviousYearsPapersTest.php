<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\Paper;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreviousYearsPapersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        AcademicSession::createFirstSession();
    }

    /** @test */
    public function users_associated_with_a_course_can_see_all_previous_papers_and_comments_associated_with_it(): void
    {
        $this->withoutExceptionHandling();
        $session1 = AcademicSession::factory()->create(['session' => '1980/1981']);
        $session2 = AcademicSession::factory()->create(['session' => '1981/1982']);
        $session1->setAsDefault();
        $setter = User::factory()->create();
        $course1 = Course::factory()->create(['code' => 'ENG1234', 'academic_session_id' => $session1->id]);
        $course2 = Course::factory()->create(['code' => 'ENG1234', 'academic_session_id' => $session2->id]);
        $course3 = Course::factory()->create(['code' => 'ENG5678', 'academic_session_id' => $session2->id]);
        $setter->markAsSetter($course1);
        $paper1 = Paper::factory()->create(['course_id' => $course1->id]);
        $paper2 = Paper::factory()->create(['course_id' => $course2->id]);
        $paper3 = Paper::factory()->create(['course_id' => $course3->id]);

        $response = $this->actingAs($setter)->get(route('course.all_papers', $course1->id));

        $response->assertOk();
        $response->assertSee($paper1->original_filename);
        $response->assertSee($paper2->original_filename);
        $response->assertDontSee($paper3->filename);
    }

    /** @test */
    public function users_not_associated_with_a_course_cant_view_the_previous_papers(): void
    {
        $session1 = AcademicSession::factory()->create(['session' => '1980/1981']);
        $session2 = AcademicSession::factory()->create(['session' => '1981/1982']);
        $session1->setAsDefault();
        $notTheSetter = User::factory()->create();
        $course1 = Course::factory()->create(['code' => 'ENG1234', 'academic_session_id' => $session1->id]);
        $course2 = Course::factory()->create(['code' => 'ENG1234', 'academic_session_id' => $session2->id]);
        $course3 = Course::factory()->create(['code' => 'ENG5678', 'academic_session_id' => $session2->id]);
        $paper1 = Paper::factory()->create(['course_id' => $course1->id]);
        $paper2 = Paper::factory()->create(['course_id' => $course2->id]);
        $paper3 = Paper::factory()->create(['course_id' => $course3->id]);

        $response = $this->actingAs($notTheSetter)->get(route('course.all_papers', $course1->id));

        $response->assertForbidden();
    }
}
