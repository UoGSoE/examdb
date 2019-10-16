<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\WithFaker;
use App\Mail\NotifyTeachingOfficeExternalDeadline;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExternalDeadlineTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_email_is_sent_to_the_teaching_office_on_the_external_deadline_date()
    {
        Mail::fake();
        option(['teaching_office_contact_glasgow' => 'jenny@example.com']);
        option(['external_deadline_glasgow' => now()->format('Y-m-d')]);

        Artisan::call('exampapers:notifyteachingofficeexternals --area=glasgow');

        Mail::assertQueued(NotifyTeachingOfficeExternalDeadline::class, function ($mail) {
            return $mail->hasTo('jenny@example.com');
        });
    }

    /** @test */
    public function an_email_is_not_sent_to_the_teaching_office_before_the_external_deadline_date()
    {
        Mail::fake();
        option(['teaching_office_contact_glasgow' => 'jenny@example.com']);
        option(['external_deadline_glasgow' => now()->addDays(1)->format('Y-m-d')]);

        Artisan::call('exampapers:notifyteachingofficeexternals --area=glasgow');

        Mail::assertNotQueued(NotifyTeachingOfficeExternalDeadline::class);
    }

    /** @test */
    public function notifications_are_only_sent_once_per_area()
    {
        option(['teaching_office_contact_glasgow' => 'jenny@example.com']);
        option(['external_deadline_glasgow' => now()->format('Y-m-d')]);

        Mail::fake();

        Artisan::call('exampapers:notifyteachingofficeexternals --area=glasgow');

        Mail::assertQueued(NotifyTeachingOfficeExternalDeadline::class, function ($mail) {
            return $mail->hasTo('jenny@example.com');
        });

        Mail::fake();

        Artisan::call('exampapers:notifyteachingofficeexternals --area=glasgow');

        Mail::assertNotQueued(NotifyTeachingOfficeExternalDeadline::class);
    }
}
