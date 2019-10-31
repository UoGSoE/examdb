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
    public function staff_are_notified_about_any_incomplete_papers_one_day_after_the_deadline()
    {
        Mail::fake();
        $this->withoutExceptionHandling();
        // set the deadline to yesterday
        option(['internal_deadline_glasgow' => now()->subDays(1)->format('Y-m-d')]);
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
        $resitPaper1->approvedBy($moderator1);

        // at this stage the moderator1 has approved everything, but neither setter has or moderator2

        Artisan::call('exampapers:notify-paperwork-incomplete --area=glasgow');

        // check an email was sent to setter 1 & 2 and moderator2 about the course they are associated with
        // which means that the setters should get emailed, but not the moderators as only one has to do it
        Mail::assertQueued(PaperworkIncomplete::class, 2);
        Mail::assertQueued(PaperworkIncomplete::class, function ($mail) use ($setter1) {
            return $mail->hasTo($setter1->email);
        });
        Mail::assertQueued(PaperworkIncomplete::class, function ($mail) use ($setter2) {
            return $mail->hasTo($setter2->email);
        });
    }

    /** @test */
    public function staff_are_notified_about_any_incomplete_papers_one_week_before_the_deadline()
    {
        Mail::fake();
        $this->withoutExceptionHandling();
        // set the deadline in a weeks time
        option(['internal_deadline_glasgow' => now()->addWeeks(1)->format('Y-m-d')]);
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
        $resitPaper1->approvedBy($moderator1);

        // at this stage the moderator1 has approved everything, but neither setter has or moderator2

        Artisan::call('exampapers:notify-paperwork-incomplete --area=glasgow');

        // check an email was sent to setter 1 & 2 and moderator2 about the course they are associated with
        // which means that the setters should get emailed, but not the moderators as only one has to do it
        Mail::assertQueued(PaperworkIncomplete::class, 2);
        Mail::assertQueued(PaperworkIncomplete::class, function ($mail) use ($setter1) {
            return $mail->hasTo($setter1->email);
        });
        Mail::assertQueued(PaperworkIncomplete::class, function ($mail) use ($setter2) {
            return $mail->hasTo($setter2->email);
        });
    }

    /** @test */
    public function staff_are_not_sent_an_emails_if_it_is_not_one_week_before_the_deadline_or_one_day_after()
    {
        Mail::fake();

        // set the deadline to some time way in the future
        option(['internal_deadline_glasgow' => now()->addDays(54)->format('Y-m-d')]);

        // the Paper::PAPER_CHECKLIST is the trigger that means 'this paper is ready'
        $paper = create(Paper::class, ['category' => 'main']);
        $setter = create(User::class);
        $setter->markAsSetter($paper->course);

        Artisan::call('exampapers:notify-paperwork-incomplete --area=glasgow');

        Mail::assertNotQueued(PaperworkIncomplete::class);
    }

    /** @test */
    public function staff_are_not_notified_about_papers_which_are_fully_set()
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
        $mainPaper->approvedBy($setter1);
        $mainPaper->approvedBy($moderator1);
        $resitPaper->approvedBy($setter1);
        $resitPaper->approvedBy($moderator1);

        Artisan::call('exampapers:notify-paperwork-incomplete --area=glasgow');

        // check an email wasn't sent to anyone
        Mail::assertNotQueued(PaperworkIncomplete::class);
    }

    /** @test */
    public function staff_are_not_notified_about_disabled_courses()
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
        Mail::assertNotQueued(PaperworkIncomplete::class);
    }

    /** @test */
    public function disabled_staff_are_not_notified()
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
        $setter1->delete();

        Artisan::call('exampapers:notify-paperwork-incomplete --area=glasgow');

        // check an email wasn't sent to anyone
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

        // check an email wasn sent only to the setter
        Mail::assertQueued(PaperworkIncomplete::class, 1);
        Mail::assertQueued(PaperworkIncomplete::class, function ($mail) use ($setter1) {
            return $mail->hasTo($setter1->email);
        });
    }
}
