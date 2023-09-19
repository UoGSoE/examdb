<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\Discipline;
use App\Jobs\NotifyExternals;
use App\Mail\ExternalHasPapersToLookAt;
use App\Mail\NotifyExternalSpecificCourse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NotifyExternalsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        AcademicSession::createFirstSession();
    }

    /** @test */
    public function admins_can_trigger_a_job_to_notify_externals_to_look_at_the_system()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $discipline = create(Discipline::class);
        Bus::fake();

        $response = $this->actingAs($admin)->post(route('admin.notify.externals', [
            'area' => $discipline->title,
        ]));

        $response->assertStatus(302);
        $response->assertSessionDoesntHaveErrors();

        Bus::assertDispatched(NotifyExternals::class, function ($job) use ($discipline) {
            return $job->area === $discipline->title;
        });
    }

    /** @test */
    public function non_admins_cant_trigger_a_job_to_notify_externals()
    {
        $user = create(User::class);
        $discipline = create(Discipline::class);

        Bus::fake();

        $response = $this->actingAs($user)->post(route('admin.notify.externals', [
            'area' => $discipline->title,
        ]));

        $response->assertStatus(403);

        Bus::assertNotDispatched(NotifyExternals::class);
    }

    /** @test */
    public function external_notifications_only_go_to_externals_about_courses_in_the_current_semester()
    {
        Mail::fake();
        Storage::fake('exampapers');
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        login($admin);
        $external1 = create(User::class);
        $external2 = create(User::class);
        $discipline = create(Discipline::class);
        $course1 = create(Course::class, ['semester' => 1, 'discipline_id' => $discipline->id]);
        $course2 = create(Course::class, ['semester' => 2, 'discipline_id' => $discipline->id]);
        $course1->addPaper('main', 'blah de blah', UploadedFile::fake()->create('main_paper_1.pdf'));
        $course2->addPaper('main', 'blah de blah', UploadedFile::fake()->create('main_paper_1.pdf'));
        $external1->markAsExternal($course1);
        $external1->markAsExternal($course2);
        $external2->markAsExternal($course2);
        option(['start_semester_1' => now()->format('Y-m-d')]);
        option(['start_semester_2' => now()->addWeek()->format('Y-m-d')]);
        option(['start_semester_3' => now()->addMonth()->format('Y-m-d')]);

        NotifyExternals::dispatch($discipline->title);

        Mail::assertQueued(ExternalHasPapersToLookAt::class, 1);
        Mail::assertQueued(ExternalHasPapersToLookAt::class, function ($mail) use ($external1) {
            return $mail->hasTo($external1->email);
        });
    }

    /** @test */
    public function external_notifications_only_go_to_externals_about_courses_that_have_papers()
    {
        Mail::fake();
        Storage::fake('exampapers');
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        login($admin);
        $external1 = create(User::class);
        $external2 = create(User::class);
        $discipline = create(Discipline::class);
        $course1 = create(Course::class, ['semester' => 1, 'discipline_id' => $discipline->id]);
        $course2 = create(Course::class, ['semester' => 1, 'discipline_id' => $discipline->id]);
        $course1->addPaper('main', 'blah de blah', UploadedFile::fake()->create('main_paper_1.pdf'));
        $external1->markAsExternal($course1);
        $external1->markAsExternal($course2);
        $external2->markAsExternal($course2);
        option(['start_semester_1' => now()->format('Y-m-d')]);
        option(['start_semester_2' => now()->addWeek()->format('Y-m-d')]);
        option(['start_semester_3' => now()->addMonth()->format('Y-m-d')]);

        NotifyExternals::dispatch($discipline->title);

        Mail::assertQueued(ExternalHasPapersToLookAt::class, 1);
        Mail::assertQueued(ExternalHasPapersToLookAt::class, function ($mail) use ($external1) {
            return $mail->hasTo($external1->email);
        });
    }

    /** @test */
    public function admins_can_manually_notify_the_externals_for_a_given_course()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $course = create(Course::class);
        $external1 = create(User::class);
        $external1->markAsExternal($course);
        $external2 = create(User::class);
        $external2->markAsExternal($course);
        $external3 = create(User::class);
        Mail::fake();

        $response = $this->actingAs($admin)->post(route('admin.notify.externals_course', $course->id));

        $response->assertStatus(200);
        $response->assertSessionDoesntHaveErrors();

        Mail::assertQueued(NotifyExternalSpecificCourse::class, 2); // 2 mails queued (external3 shouldn't get one)
        Mail::assertQueued(NotifyExternalSpecificCourse::class, function ($mail) use ($external1) {
            return $mail->hasTo($external1->email);
        });
        Mail::assertQueued(NotifyExternalSpecificCourse::class, function ($mail) use ($external2) {
            return $mail->hasTo($external2->email);
        });
    }

    /** @test */
    public function manually_notifying_the_externals_for_a_course_marks_the_course_as_having_notified_the_externals()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $course = create(Course::class);
        $external1 = create(User::class);
        $external1->markAsExternal($course);
        $external2 = create(User::class);
        $external2->markAsExternal($course);
        $external3 = create(User::class);
        Mail::fake();

        $this->assertNull($course->externalNotified());

        $response = $this->actingAs($admin)->post(route('admin.notify.externals_course', $course->id));

        $response->assertStatus(200);
        $response->assertSessionDoesntHaveErrors();

        $this->assertTrue($course->fresh()->externalNotified());
    }
}
