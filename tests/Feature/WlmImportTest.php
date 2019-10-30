<?php
// @codingStandardsIgnoreFile

namespace Tests\Feature;

use App\User;
use App\Course;
use Carbon\Carbon;
use App\Discipline;
use Tests\TestCase;
use App\Wlm\WlmClient;
use App\Wlm\WlmImporter;
use App\Wlm\FakeWlmClient;
use App\Jobs\ImportFromWlm;
use App\Wlm\WlmClientInterface;
use App\Events\WlmImportComplete;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Mail\WlmImportComplete as MailableWlmImportComplete;

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
        $this->assertCount(2, Discipline::all());
        Course::all()->each(function ($course) {
            $this->assertCount(0, $course->staff()->get());
            $this->assertNotNull($course->discipline);
        });
        User::all()->each(function ($staff) {
            $this->assertEquals("{$staff->username}@glasgow.ac.uk", $staff->email);
        });
        $courseA = Course::first();
        $this->assertEquals('TEST1234', $courseA->code);
        $this->assertEquals('Fake Course 1234', $courseA->title);
        $this->assertEquals('Discipline the first', $courseA->discipline->title);
    }

    /** @test */
    public function can_limit_course_numbers_while_importing_the_data_from_the_fake_wlm()
    {
        $importer = new WlmImporter(new FakeWlmClient);

        $importer->run(1);

        $this->assertCount(1, Course::all());
        $this->assertCount(2, User::all());
        Course::all()->each(function ($course) {
            $this->assertCount(0, $course->staff()->get());
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
        $user = create(User::class, ['username' => 'test', 'email' => 'test@glasgow.ac.uk']);

        ImportFromWlm::dispatch($user);

        $this->assertEquals(2, Course::count());
        $this->assertCount(4, User::all());
        Course::all()->each(function ($course) {
            $this->assertCount(0, $course->staff()->get());
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
    public function the_wlm_complete_event_triggers_an_email_to_the_user_who_initiated_the_sync()
    {
        Mail::fake();
        $user = create(User::class);

        WlmImportComplete::dispatch($user);

        Mail::assertQueued(MailableWlmImportComplete::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
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
