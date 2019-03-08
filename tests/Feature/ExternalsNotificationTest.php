<?php

namespace Tests\Feature;

use App\User;
use App\Paper;
use Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use App\Mail\ExternalHasPapersToLookAt;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExternalsNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function externals_are_not_sent_an_emails_if_it_is_currently_before_the_notification_deadline()
    {
        Mail::fake();
        $this->withoutExceptionHandling();

        // set the deadline to tomorrow
        option(['externals_notification_date' => now()->addDays(1)->format('Y-m-d')]);

        // the 'Paper Checklist' is the trigger that means 'this paper is ready'
        $paper = create(Paper::class, ['subcategory' => 'Paper Checklist']);
        $external1 = create(User::class);
        $external1->markAsExternal($paper->course);

        Artisan::call('exampapers:notify-externals');

        Mail::assertNotQueued(ExternalHasPapersToLookAt::class);
    }

    /** @test */
    public function externals_are_notified_about_any_fully_set_papers_after_the_deadline()
    {
        Mail::fake();
        $this->withoutExceptionHandling();
        // set the deadline to yesterday
        option(['externals_notification_date' => now()->subDays(1)->format('Y-m-d')]);
        $external1 = create(User::class);
        $external2 = create(User::class);
        // the 'Paper Checklist' is the trigger that means 'this paper is ready'
        $paper1 = create(Paper::class, ['subcategory' => 'Paper Checklist']);
        $external1->markAsExternal($paper1->course);
        $external2->markAsExternal($paper1->course);

        Artisan::call('exampapers:notify-externals');

        // check an email was sent to both externals about the course they are associated with
        Mail::assertQueued(ExternalHasPapersToLookAt::class, 2);
        Mail::assertQueued(ExternalHasPapersToLookAt::class, function ($mail) use ($external1) {
            return $mail->hasTo($external1->email);
        });
        Mail::assertQueued(ExternalHasPapersToLookAt::class, function ($mail) use ($external2) {
            return $mail->hasTo($external2->email);
        });
    }

    /** @test */
    public function externals_are_not_notified_about_papers_which_are_not_fully_set()
    {
        Mail::fake();
        $this->withoutExceptionHandling();
        // set the deadline to yesterday
        option(['externals_notification_date' => now()->subDays(1)->format('Y-m-d')]);
        // the 'Paper Checklist' is the trigger that means 'this paper is ready'
        $paper1 = create(Paper::class, ['subcategory' => 'Not Paper Checklist']);
        $external1 = create(User::class);
        $external1->markAsExternal($paper1->course);

        Artisan::call('exampapers:notify-externals');

        // check an email wasn't sent to the external
        Mail::assertNotQueued(ExternalHasPapersToLookAt::class);
    }

    /** @test */
    public function externals_are_not_notified_about_resit_papers_before_the_resit_deadline()
    {
        $this->fail('TODO');
    }
}
