<?php

namespace Tests\Feature;

use App\User;
use App\Paper;
use App\Course;
use Tests\TestCase;
use App\AcademicSession;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LivewirePaperChecklistTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        AcademicSession::createFirstSession();
    }

    /** @test */
    public function we_can_see_the_paper_checklist_page_for_a_course()
    {
        $this->withoutExceptionHandling();
        $course = create(Course::class);
        $user = create(User::class);
        $user->markAsSetter($course);
        $paper = create(Paper::class, ['course_id' => $course->id, 'category' => 'main']);

        $response = $this->actingAs($user)->get(route('course.checklist.create', [
            'course' => $course->id,
            'category' => 'main',
        ]));

        $response->assertOk();
        $response->assertViewHas('course', $course);
        $response->assertSeeLivewire('paper-checklist');
    }
}
