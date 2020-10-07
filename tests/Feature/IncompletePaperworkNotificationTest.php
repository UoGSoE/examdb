<?php

namespace Tests\Feature;

use App\Mail\IncompleteCourses;
use App\Mail\PaperworkIncomplete;
use App\Paper;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * I think this is entirely redundant now and all moved to the 'TimedNotificationsTest'
 */
class IncompletePaperworkNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function moderators_are_notified_about_any_incomplete_papers_one_day_after_the_deadline()
    {
        Mail::fake();
        $this->withoutExceptionHandling();
        // set the deadline to yesterday
        option(['internal_deadline_glasgow' => now()->subDays(1)->format('Y-m-d')]);
        option(['teaching_office_contact_glasgow' => 'someone@example.com']);
        $setter1 = create(User::class);
        $setter2 = create(User::class);
        $moderator1 = create(User::class);
        $moderator2 = create(User::class);
        $setter3 = create(User::class); // an extra just to make sure they are _not_ emailed in the batch
        $mainPaper1 = create(Paper::class, ['category' => 'main']);
        $resitPaper1 = create(Paper::class, ['course_id' => $mainPaper1->course_id, 'category' => 'resit']);
        $setter1->markAsSetter($mainPaper1->course);
        $setter2->markAsSetter($mainPaper1->course);
        $moderator1->markAsModerator($mainPaper1->course);
        $moderator2->markAsModerator($mainPaper1->course);
        $mainPaper1->approvedBy($moderator1);

        // at this stage the moderator1 has approved the main paper, but the resit paper is still waiting

        Artisan::call('exampapers:notify-paperwork-incomplete --area=glasgow');

        // check an email was sent to moderator1 and moderator2 about the course they are associated with
        // both the main and resit papers have to be approved, so both will be notified
        Mail::assertQueued(PaperworkIncomplete::class, 2);
        Mail::assertQueued(PaperworkIncomplete::class, function ($mail) use ($moderator1) {
            return $mail->hasTo($moderator1->email);
        });
        Mail::assertQueued(PaperworkIncomplete::class, function ($mail) use ($moderator2) {
            return $mail->hasTo($moderator2->email);
        });
    }

    /** @test */
    public function moderators_are_notified_about_any_incomplete_papers_one_week_before_the_deadline()
    {
        Mail::fake();
        $this->withoutExceptionHandling();
        // set the deadline in a weeks time
        option(['internal_deadline_glasgow' => now()->addWeeks(1)->format('Y-m-d')]);
        option(['teaching_office_contact_glasgow' => 'someone@example.com']);
        $setter1 = create(User::class);
        $setter2 = create(User::class);
        $moderator1 = create(User::class);
        $moderator2 = create(User::class);
        $setter3 = create(User::class); // an extra just to make sure they are _not_ emailed in the batch
        $mainPaper1 = create(Paper::class, ['category' => 'main']);
        $resitPaper1 = create(Paper::class, ['course_id' => $mainPaper1->course_id, 'category' => 'resit']);
        $setter1->markAsSetter($mainPaper1->course);
        $setter2->markAsSetter($mainPaper1->course);
        $moderator1->markAsModerator($mainPaper1->course);
        $moderator2->markAsModerator($mainPaper1->course);
        $resitPaper1->approvedBy($moderator1);

        // at this stage the moderator1 has approved the main paper, but the resit paper is still waiting

        Artisan::call('exampapers:notify-paperwork-incomplete --area=glasgow');

        // check an email was sent to moderator1 and moderator2 about the course they are associated with
        // both the main and resit papers have to be approved, so both will be notified
        Mail::assertQueued(PaperworkIncomplete::class, 2);
        Mail::assertQueued(PaperworkIncomplete::class, function ($mail) use ($moderator1) {
            return $mail->hasTo($moderator1->email);
        });
        Mail::assertQueued(PaperworkIncomplete::class, function ($mail) use ($moderator2) {
            return $mail->hasTo($moderator2->email);
        });
    }

    /** @test */
    public function moderators_are_not_sent_an_emails_if_it_is_not_one_week_before_the_deadline_or_one_day_after()
    {
        Mail::fake();

        // set the deadline to some time way in the future
        option(['internal_deadline_glasgow' => now()->addDays(54)->format('Y-m-d')]);

        $paper = create(Paper::class, ['category' => 'main']);
        $moderator = create(User::class);
        $moderator->markAsModerator($paper->course);

        Artisan::call('exampapers:notify-paperwork-incomplete --area=glasgow');

        Mail::assertNothingQueued();
    }

    /** @test */
    public function moderators_and_the_teaching_office_are_not_notified_about_papers_which_are_fully_set()
    {
        Mail::fake();
        $this->withoutExceptionHandling();
        // set the deadline to yesterday
        option(['internal_deadline_glasgow' => now()->subDays(1)->format('Y-m-d')]);
        $mainPaper = create(Paper::class, ['category' => 'main']);
        $resitPaper = create(Paper::class, ['course_id' => $mainPaper->course_id, 'category' => 'resit']);
        $setter1 = create(User::class);
        $moderator1 = create(User::class);
        $moderator2 = create(User::class);
        $setter1->markAsSetter($mainPaper->course);
        $moderator1->markAsModerator($mainPaper->course);
        $moderator2->markAsModerator($mainPaper->course);
        $mainPaper->approvedBy($moderator1);
        $resitPaper->approvedBy($moderator2);

        Artisan::call('exampapers:notify-paperwork-incomplete --area=glasgow');

        // check an email wasn't sent to anyone
        Mail::assertNotQueued(PaperworkIncomplete::class);
        Mail::assertNotQueued(IncompleteCourses::class);
    }

    /** @test */
    public function moderators_are_not_notified_about_disabled_courses()
    {
        Mail::fake();
        $this->withoutExceptionHandling();
        // set the deadline to yesterday
        option(['internal_deadline_glasgow' => now()->subDays(1)->format('Y-m-d')]);
        $mainPaper = create(Paper::class, ['category' => 'main']);
        $resitPaper = create(Paper::class, ['course_id' => $mainPaper->course_id, 'category' => 'resit']);
        $setter1 = create(User::class);
        $moderator1 = create(User::class);
        $setter1->markAsSetter($mainPaper->course);
        $moderator1->markAsModerator($mainPaper->course);
        $mainPaper->course->disable();

        Artisan::call('exampapers:notify-paperwork-incomplete --area=glasgow');

        // check an email wasn't sent to anyone
        Mail::assertNothingQueued();
    }

    /** @test */
    public function disabled_staff_are_not_notified()
    {
        Mail::fake();
        $this->withoutExceptionHandling();
        // set the deadline to yesterday
        option(['internal_deadline_glasgow' => now()->subDays(1)->format('Y-m-d')]);
        option(['teaching_office_contact_glasgow' => 'someone@example.com']);
        $mainPaper = create(Paper::class, ['category' => 'main']);
        $resitPaper = create(Paper::class, ['course_id' => $mainPaper->course_id, 'category' => 'resit']);
        $moderator1 = create(User::class);
        $moderator1->markAsModerator($mainPaper->course);
        $moderator2 = create(User::class);
        $moderator2->markAsModerator($mainPaper->course);
        $moderator2->delete();

        Artisan::call('exampapers:notify-paperwork-incomplete --area=glasgow');

        // check an email was only sent to moderator 1
        Mail::assertQueued(PaperworkIncomplete::class, 1);
        Mail::assertQueued(PaperworkIncomplete::class, function ($mail) use ($moderator1) {
            return $mail->hasTo($moderator1->email);
        });
    }

    /** @test */
    public function externals_are_not_notified_about_any_of_this_stuff()
    {
        Mail::fake();
        $this->withoutExceptionHandling();
        // set the deadline to yesterday
        option(['internal_deadline_glasgow' => now()->subDays(1)->format('Y-m-d')]);
        $mainPaper = create(Paper::class, ['category' => 'main']);
        $resitPaper = create(Paper::class, ['course_id' => $mainPaper->course_id, 'category' => 'resit']);
        $setter1 = create(User::class);
        $moderator1 = create(User::class);
        $external1 = create(User::class);
        $setter1->markAsSetter($mainPaper->course);
        $moderator1->markAsModerator($mainPaper->course);
        $external1->markAsExternal($mainPaper->course);
        $mainPaper->approvedBy($moderator1);
        $resitPaper->approvedBy($moderator1);

        Artisan::call('exampapers:notify-paperwork-incomplete --area=glasgow');

        Mail::assertNotQueued(PaperworkIncomplete::class);
    }

    /** @test */
    public function the_teaching_office_are_notified_about_incomplete_courses()
    {
        Mail::fake();
        $this->withoutExceptionHandling();
        // set the deadline to yesterday
        option(['internal_deadline_glasgow' => now()->subDays(1)->format('Y-m-d')]);
        option(['teaching_office_contact_glasgow' => 'someone@example.com']);
        $moderator1 = create(User::class);
        $mainPaper1 = create(Paper::class, ['category' => 'main']);
        $mainPaper2 = create(Paper::class, ['category' => 'main']);
        $mainPaper3 = create(Paper::class, ['category' => 'main']);
        $moderator1->markAsModerator($mainPaper1->course);
        $moderator1->markAsModerator($mainPaper2->course);
        $moderator1->markAsModerator($mainPaper3->course);
        $mainPaper2->approvedBy($moderator1);

        // at this stage mainPaper 1 & 3 are not approved

        Artisan::call('exampapers:notify-paperwork-incomplete --area=glasgow');

        // check an email was sent to the teaching office with the unapproved courses
        Mail::assertQueued(IncompleteCourses::class, function ($mail) use ($mainPaper1, $mainPaper3) {
            return $mail->courses = collect([$mainPaper1->course, $mainPaper3->course]) &&
                   $mail->hasTo('someone@example.com');
        });
    }
}
