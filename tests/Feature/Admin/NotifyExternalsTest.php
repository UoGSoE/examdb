<?php

namespace Tests\Feature\Admin;

use App\User;
use App\Course;
use Tests\TestCase;
use App\Jobs\NotifyExternals;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Mail\ExternalHasPapersToLookAt;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

        Mail::assertQueued(ExternalHasPapersToLookAt::class, 2); // 2 mails queued (external3 shouldn't get one)
        Mail::assertQueued(ExternalHasPapersToLookAt::class, function ($mail) use ($external1) {
            return $mail->hasTo($external1->email);
        });
        Mail::assertQueued(ExternalHasPapersToLookAt::class, function ($mail) use ($external2) {
            return $mail->hasTo($external2->email);
        });
    }
}
