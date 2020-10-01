<?php

// @codingStandardsIgnoreFile

namespace Tests\Feature;

use App\Course;
use App\Discipline;
use App\Events\WlmImportComplete;
use App\Jobs\ImportFromWlm;
use App\Mail\WlmImportComplete as MailableWlmImportComplete;
use App\User;
use App\Wlm\FakeWlmClient;
use App\Wlm\WlmClient;
use App\Wlm\WlmClientInterface;
use App\Wlm\WlmImporter;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WlmImportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_import_the_data_from_the_fake_wlm()
    {
        /**
         * See the FakeWlmClient getCourse1() and getCourse2() for where all of these numbers & data comes from
         */

        $importer = new WlmImporter(new FakeWlmClient);

        $importer->run();

        $this->assertCount(2, Course::all());
        $this->assertCount(7, User::all());
        $this->assertCount(2, Discipline::all());

        $course1 = Course::findByCode('TEST1234');
        $this->assertEquals('Fake Course 1234', $course1->title);
        $this->assertEquals('Discipline the first', $course1->discipline->title);
        $this->assertCount(6, $course1->staff()->get());

        $course2 = Course::findByCode('TEST4321');
        $this->assertEquals('Fake Course 4321', $course2->title);
        $this->assertEquals('Discipline the second', $course2->discipline->title);
        $this->assertCount(3, $course2->staff()->get());

        // check the right staff have been allocated for $course1
        $setter1 = User::findByUsername('fake1x');
        $this->assertTrue($setter1->isSetterFor($course1));
        $this->assertFalse($setter1->isModeratorFor($course1));
        $this->assertFalse($setter1->isExternalFor($course1));
        $setter2 = User::findByUsername('blah2y');
        $this->assertTrue($setter2->isSetterFor($course1));
        $this->assertFalse($setter2->isModeratorFor($course1));
        $this->assertFalse($setter2->isExternalFor($course1));

        $moderator1 = User::findByUsername('fake2x');
        $this->assertFalse($moderator1->isSetterFor($course1));
        $this->assertTrue($moderator1->isModeratorFor($course1));
        $this->assertFalse($moderator1->isExternalFor($course1));
        $moderator2 = User::findByUsername('blah3y');
        $this->assertFalse($moderator2->isSetterFor($course1));
        $this->assertTrue($moderator2->isModeratorFor($course1));
        $this->assertFalse($moderator2->isExternalFor($course1));

        $external1 = User::findByUsername('fake3x');
        $this->assertFalse($external1->isSetterFor($course1));
        $this->assertFalse($external1->isModeratorFor($course1));
        $this->assertTrue($external1->isExternalFor($course1));
        $external2 = User::findByUsername('blah4y');
        $this->assertFalse($external2->isSetterFor($course1));
        $this->assertFalse($external2->isModeratorFor($course1));
        $this->assertTrue($external2->isExternalFor($course1));

        // check the right staff have been allocated for $course2
        $setter1 = User::findByUsername('doc2w');
        $this->assertTrue($setter1->isSetterFor($course2));
        $this->assertFalse($setter1->isModeratorFor($course2));
        $this->assertFalse($setter1->isExternalFor($course2));

        $setterModerator1 = User::findByUsername('blah2y');
        $this->assertTrue($setterModerator1->isSetterFor($course2));
        $this->assertTrue($setterModerator1->isModeratorFor($course2));
        $this->assertFalse($setterModerator1->isExternalFor($course2));

        $external1 = User::findByUsername('fake2x');
        $this->assertFalse($external1->isSetterFor($course2));
        $this->assertFalse($external1->isModeratorFor($course2));
        $this->assertTrue($external1->isExternalFor($course2));

        User::all()->each(function ($staff) {
            $this->assertEquals("{$staff->username}@glasgow.ac.uk", $staff->email);
        });
    }

    /** @test */
    public function importing_data_doesnt_duplicate_or_crash_on_soft_deleted_entries()
    {
        $deletedUser = create(User::class, ['username' => 'fake1x']);
        $deletedUser->delete();
        $deletedCourse = create(Course::class, ['code' => 'TEST1234']);
        $deletedCourse->delete();
        $importer = new WlmImporter(new FakeWlmClient);

        $importer->run();

        $this->assertCount(2, Course::withTrashed()->get());
        $this->assertCount(7, User::withTrashed()->get());
        $this->assertNotNull($deletedUser->deleted_at);
        $this->assertNotNull($deletedCourse->deleted_at);
    }

    /** @test */
    public function can_limit_course_numbers_while_importing_the_data_from_the_fake_wlm()
    {
        $importer = new WlmImporter(new FakeWlmClient);

        $importer->run(1);

        $this->assertCount(1, Course::all());
        $this->assertCount(6, User::all());
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
        $this->assertCount(8, User::all());
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
