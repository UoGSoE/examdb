<?php
// @codingStandardsIgnoreFile

namespace Tests\Feature;

use App\Course;
use App\Events\WlmImportComplete;
use App\Jobs\ImportFromWlm;
use App\User;
use App\Wlm\FakeWlmClient;
use App\Wlm\WlmClient;
use App\Wlm\WlmClientInterface;
use App\Wlm\WlmImporter;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WlmImportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_import_the_data_from_the_fake_wlm()
    {
        $importer = new WlmImporter(new FakeWlmClient);

        $importer->run();

        $this->assertCount(2, Course::all());
        $this->assertCount(3, User::all());
        Course::all()->each(function ($course) {
            $this->assertCount(2, $course->staff()->get());
        });
        User::all()->each(function ($staff) {
            $this->assertEquals("{$staff->username}@glasgow.ac.uk", $staff->email);
        });
        $courseA = Course::first();
        $this->assertEquals('TEST1234', $courseA->code);
        $this->assertEquals('Fake Course 1234', $courseA->title);
    }

    /** @test */
    public function if_there_are_no_setters_for_a_course_then_staff_teaching_it_in_the_wlm_are_made_setters_by_default()
    {
        $course1 = create(Course::class, ['code' => 'TEST1234']);
        $course2 = create(Course::class, ['code' => 'TEST4321']);
        $existingSetter = create(User::class);
        $existingSetter->markAsSetter($course1);
        $this->assertTrue($existingSetter->fresh()->isSetterFor($course1));
        $importer = new WlmImporter(new FakeWlmClient);

        $importer->run();

        $this->assertCount(2, Course::all());
        $this->assertCount(4, User::all());

        // the existing setter should still be there as setter for $course1
        $this->assertTrue($existingSetter->fresh()->isSetterFor($course1));
        // and the new user imported from the WLM should _not_ be marked as a setter for $course1 as
        // it had an existing setter
        $newStaff = $course1->staff()->where('username', '=', 'fake1x')->first();
        $this->assertFalse($newStaff->isSetterFor($course1));
        // but all of the new staff for $course2 _should_ be marked as setters as it had no existing setters
        $course2->staff->each(function ($staff) use ($course2) {
            $this->assertTrue($staff->isSetterFor($course2));
        });
    }

    /** @test */
    public function can_limit_course_numbers_while_importing_the_data_from_the_fake_wlm()
    {
        $importer = new WlmImporter(new FakeWlmClient);

        $importer->run(1);

        $this->assertCount(1, Course::all());
        $this->assertCount(2, User::all());
        Course::all()->each(function ($course) {
            $this->assertCount(2, $course->staff()->get());
        });
    }

    /** @test */
    public function data_not_in_the_wlm_can_be_removed_from_the_local_db_after_import()
    {
        $this->markTestSkipped('TODO: Waiting to find out what should happen in this case');
        $student = $this->createStudent();
        $assessment = $this->createAssessment(['deadline' => Carbon::now()->subWeeks(10)]);
        $course = $assessment->course;
        $course->students()->sync([$student->id]);
        $student->recordFeedback($assessment);
        $staff = $assessment->staff;
        $importer = new WlmImporter(new FakeWlmClient);

        $importer->sync();

        $this->assertDatabaseMissing('users', ['id' => $staff->id]);
        $this->assertDatabaseMissing('users', ['id' => $student->id]);
        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
        $this->assertDatabaseMissing('assessment_feedbacks', ['student_id' => $student->id]);
        //$this->assertEquals(0, AssessmentFeedback::count());
        $this->assertCount(2, Course::all());
        $this->assertCount(3, User::staff()->get());
        $this->assertCount(3, User::student()->get());
    }

    /** @test */
    public function we_can_dispatch_a_job_which_runs_the_wlm_import_which_fires_an_event_when_complete()
    {
        Event::fake();
        app()->singleton(WlmClientInterface::class, function () {
            return new FakeWlmClient;
        });

        ImportFromWlm::dispatch();

        $this->assertCount(2, Course::all());
        $this->assertCount(3, User::all());
        Course::all()->each(function ($course) {
            $this->assertCount(2, $course->staff()->get());
        });
        User::all()->each(function ($staff) {
            $this->assertEquals("{$staff->username}@glasgow.ac.uk", $staff->email);
        });
        $courseA = Course::first();
        $this->assertEquals('TEST1234', $courseA->code);
        $this->assertEquals('Fake Course 1234', $courseA->title);
        Event::assertDispatched(WlmImportComplete::class);
    }

    /** @test */
    public function admins_can_trigger_a_wlm_import_via_the_web()
    {
        Queue::fake();
        app()->singleton(WlmClientInterface::class, function () {
            return new FakeWlmClient;
        });

        $admin = create(User::class, ['username' => 'ADMIN', 'is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('wlm.import'));

        $response->assertOk();
        Queue::assertPushed(ImportFromWlm::class);
    }

    /**
     * @test
     * @group integration
     */
    public function can_import_the_data_from_the_real_wlm()
    {
        // $importer = new WlmImporter(new WlmClient);

        // $importer->run(50);

        // $this->assertGreaterThan(0, Course::count());
        // $this->assertGreaterThan(0, User::count());
    }

}
