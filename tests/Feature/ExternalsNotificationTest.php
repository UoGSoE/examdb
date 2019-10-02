<?php

namespace Tests\Feature;

use App\User;
use App\Paper;
use Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use App\Mail\ExternalHasPapersToLookAt;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Course;

class ExternalsNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function externals_are_not_sent_an_email_if_it_is_currently_before_the_paper_notification_deadline()
    {
        Mail::fake();
        $this->withoutExceptionHandling();

        // set the deadline to tomorrow
        option(['main_deadline' => now()->addDays(1)->format('Y-m-d')]);

        $paper = create(Paper::class);
        $external1 = create(User::class);
        $external1->markAsExternal($paper->course);

        Artisan::call('exampapers:notify-externals');

        Mail::assertNotQueued(ExternalHasPapersToLookAt::class);
    }

    /** @test */
    public function externals_are_notified_about_any_papers_for_courses_after_the_deadline()
    {
        Mail::fake();
        $this->withoutExceptionHandling();
        // set the deadline to yesterday
        option(['main_deadline' => now()->subDays(1)->format('Y-m-d')]);
        $external1 = create(User::class);
        $external2 = create(User::class);
        $external3 = create(User::class); // just to check this external isn't notified
        $paper1 = create(Paper::class);
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
    public function externals_are_not_notified_twice_about_the_same_course()
    {
        $this->withoutExceptionHandling();
        // set the deadline to yesterday
        option(['main_deadline' => now()->subDays(1)->format('Y-m-d')]);
        $paper1 = create(Paper::class);
        $external1 = create(User::class);
        $external1->markAsExternal($paper1->course);

        Mail::fake();

        Artisan::call('exampapers:notify-externals');

        Mail::assertQueued(ExternalHasPapersToLookAt::class);

        Mail::fake();

        Artisan::call('exampapers:notify-externals');

        Mail::assertNotQueued(ExternalHasPapersToLookAt::class);
    }

    /** @test */
    public function externals_are_not_notified_about_courses_with_no_papers()
    {
        $this->withoutExceptionHandling();
        // set the deadline to yesterday
        option(['main_deadline' => now()->subDays(1)->format('Y-m-d')]);
        $course = create(Course::class);
        $external1 = create(User::class);
        $external1->markAsExternal($course);

        Mail::fake();

        Artisan::call('exampapers:notify-externals');

        Mail::assertNotQueued(ExternalHasPapersToLookAt::class);
    }

    /** @test */
    public function courses_where_the_externals_have_been_notified_are_marked_as_such()
    {
        $this->withoutExceptionHandling();
        // set the deadline to yesterday
        option(['main_deadline' => now()->subDays(1)->format('Y-m-d')]);
        $paper1 = create(Paper::class);
        $external1 = create(User::class);
        $external1->markAsExternal($paper1->course);

        Mail::fake();

        $this->assertFalse($paper1->course->fresh()->externalNotified());

        Artisan::call('exampapers:notify-externals');

        $this->assertTrue($paper1->course->fresh()->externalNotified());
    }
}
