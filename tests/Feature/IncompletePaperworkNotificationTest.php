<?php

namespace Tests\Feature;

use App\User;
use App\Paper;
use Tests\TestCase;
use App\Mail\PaperworkIncomplete;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IncompletePaperworkNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function setters_are_not_sent_an_emails_if_it_is_currently_before_the_notification_deadline()
    {
        Mail::fake();
        $this->withoutExceptionHandling();

        // set the deadline to tomorrow
        option(['main_deadline' => now()->addDays(1)->format('Y-m-d')]);

        // the 'Paper Checklist' is the trigger that means 'this paper is ready'
        $paper = create(Paper::class, ['category' => 'main', 'subcategory' => 'Paper Checklist']);
        $setter = create(User::class);
        $setter->markAsSetter($paper->course);

        Artisan::call('exampapers:notify-paperwork-incomplete', ['type' => 'main']);

        Mail::assertNotQueued(PaperworkIncomplete::class);
    }

    /** @test */
    public function setters_are_notified_about_any_incomplete_papers_after_the_deadline()
    {
        Mail::fake();
        $this->withoutExceptionHandling();
        // set the deadline to yesterday
        option(['main_deadline' => now()->subDays(1)->format('Y-m-d')]);
        $setter1 = create(User::class);
        $setter2 = create(User::class);
        $setter3 = create(User::class); // an extra just to make sure they are _not_ emailed in the batch
        // the 'Paper Checklist' is the trigger that means 'this paper is ready'
        $paper1 = create(Paper::class, ['category' => 'main', 'subcategory' => 'Not Paper Checklist']);
        $setter1->markAsSetter($paper1->course);
        $setter2->markAsSetter($paper1->course);

        Artisan::call('exampapers:notify-paperwork-incomplete', ['type' => 'main']);

        // check an email was sent to both setters about the course they are associated with
        Mail::assertQueued(PaperworkIncomplete::class, 2);
        Mail::assertQueued(PaperworkIncomplete::class, function ($mail) use ($setter1) {
            return $mail->hasTo($setter1->email);
        });
        Mail::assertQueued(PaperworkIncomplete::class, function ($mail) use ($setter2) {
            return $mail->hasTo($setter2->email);
        });
    }

    /** @test */
    public function setters_are_not_notified_about_papers_which_are_fully_set()
    {
        Mail::fake();
        $this->withoutExceptionHandling();
        // set the deadline to yesterday
        option(['main_deadline' => now()->subDays(1)->format('Y-m-d')]);
        // the 'Paper Checklist' is the trigger that means 'this paper is ready'
        $paper1 = create(Paper::class, ['category' => 'main', 'subcategory' => 'Paper Checklist']);
        $setter1 = create(User::class);
        $setter1->markAsSetter($paper1->course);

        Artisan::call('exampapers:notify-paperwork-incomplete', ['type' => 'main']);

        // check an email wasn't sent to the setter
        Mail::assertNotQueued(PaperworkIncomplete::class);
    }

    /** @test */
    public function setters_are_not_notified_about_resit_papers_before_the_resit_deadline()
    {
        Mail::fake();
        $this->withoutExceptionHandling();
        // set the deadline to tomorrow
        option(['resit_deadline' => now()->addDays(1)->format('Y-m-d')]);
        // the 'Paper Checklist' is the trigger that means 'this paper is ready'
        $paper1 = create(Paper::class, ['category' => 'resit', 'subcategory' => 'Not Paper Checklist']);
        $setter1 = create(User::class);
        $setter1->markAsSetter($paper1->course);

        Artisan::call('exampapers:notify-paperwork-incomplete', ['type' => 'resit']);

        // check an email wasn't sent to the setter
        Mail::assertNotQueued(PaperworkIncomplete::class);
    }
}
