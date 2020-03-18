<?php

namespace Tests\Feature\Admin;

use App\Course;
use App\Jobs\NotifyExternals;
use App\Mail\ExternalHasPapersToLookAt;
use App\Mail\NotifyExternalSpecificCourse;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotifyExternalsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admins_can_trigger_a_job_to_notify_externals_to_look_at_the_system()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);

        Bus::fake();

        $response = $this->actingAs($admin)->post(route('admin.notify.externals', [
            'area' => 'glasgow',
        ]));

        $response->assertStatus(302);
        $response->assertSessionDoesntHaveErrors();

        Bus::assertDispatched(NotifyExternals::class, function ($job) {
            return $job->area === 'glasgow';
        });
    }

    /** @test */
    public function non_admins_cant_trigger_a_job_to_notify_externals()
    {
        $user = create(User::class);

        Bus::fake();

        $response = $this->actingAs($user)->post(route('admin.notify.externals', [
            'area' => 'glasgow',
        ]));

        $response->assertStatus(403);

        Bus::assertNotDispatched(NotifyExternals::class);
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
