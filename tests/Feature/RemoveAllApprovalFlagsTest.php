<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RemoveAllApprovalFlagsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        AcademicSession::createFirstSession();
    }

    /** @test */
    public function we_can_remove_all_approval_flags_from_all_courses()
    {
        $course1 = create(Course::class, [
            'moderator_approved_main' => true,
            'moderator_approved_resit' => true,
            'external_approved_main' => true,
            'external_approved_resit' => true,
            'moderator_approved_assessment' => true,
            'external_approved_assessment' => true,
            'external_notified' => true,
        ]);
        $course2 = create(Course::class, [
            'moderator_approved_main' => true,
            'moderator_approved_resit' => true,
            'external_approved_main' => true,
            'external_approved_resit' => true,
            'moderator_approved_assessment' => true,
            'external_approved_assessment' => true,
            'external_notified' => true,
        ]);

        $this->artisan('examdb:reset-all-approval-flags');

        tap($course1->fresh(), function ($course) {
            $this->assertFalse($course->moderator_approved_main);
            $this->assertFalse($course->moderator_approved_resit);
            $this->assertFalse($course->external_approved_main);
            $this->assertFalse($course->external_approved_resit);
            $this->assertFalse($course->moderator_approved_assessment);
            $this->assertFalse($course->external_approved_assessment);
            $this->assertFalse($course->external_notified);
        });
        tap($course2->fresh(), function ($course) {
            $this->assertFalse($course->moderator_approved_main);
            $this->assertFalse($course->moderator_approved_resit);
            $this->assertFalse($course->external_approved_main);
            $this->assertFalse($course->external_approved_resit);
            $this->assertFalse($course->moderator_approved_assessment);
            $this->assertFalse($course->external_approved_assessment);
            $this->assertFalse($course->external_notified);
        });
    }
}
