<?php

namespace Tests\Feature;

use App\Course;
use App\Paper;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TenantTestCase;
use Tests\TestCase;

class LivewirePaperChecklistTest extends TenantTestCase
{
    use RefreshDatabase;

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
