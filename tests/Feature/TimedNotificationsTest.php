<?php

namespace Tests\Feature;

use App\Course;
use App\Exceptions\TimedNotificationException;
use App\Mail\CallForPapersMail;
use App\Mail\ExternalModerationDeadlineMail;
use App\Mail\ModerationDeadlineMail;
use App\Mail\ModerationDeadlinePassedMail;
use App\Mail\NotifyExternalsReminderMail;
use App\Mail\PrintReadyDeadlineMail;
use App\Mail\PrintReadyDeadlinePassedMail;
use App\Mail\SubmissionDeadlineMail;
use App\Mail\SubmissionDeadlinePassedMail;
use App\PaperChecklist;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TimedNotificationsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function the_scheduled_command_to_run_the_timed_notifications_is_registered()
    {
        $this->assertCommandIsScheduled('examdb:timed-notifications');
    }

    /** @test */
    public function emails_are_sent_for_the_date_receive_call_for_papers_option_when_it_is_the_correct_day()
    {
        Mail::fake();
        $course1 = create(Course::class);
        $course2 = create(Course::class);
        $setter1 = create(User::class);
        $setter1->markAsSetter($course1);
        $setter1->markAsSetter($course2);
        $setter2 = create(User::class);
        $setter2->markAsSetter($course1);
        $setter2->markAsSetter($course2);
        $moderator = create(User::class);
        $moderator->markAsModerator($course1);

        option(['date_receive_call_for_papers' => now()->subHour()->format('Y-m-d')]);
        option(['start_semester_1' => now()->format('Y-m-d')]);
        option(['start_semester_2' => now()->addWeek()->format('Y-m-d')]);
        option(['start_semester_3' => now()->addMonth()->format('Y-m-d')]);

        $this->assertNull(option('date_receive_call_for_papers_email_sent_semester_1'));

        $this->artisan('examdb:timed-notifications');

        Mail::assertQueued(CallForPapersMail::class, 2);
        Mail::assertQueued(CallForPapersMail::class, function ($mail) use ($setter1) {
            return $mail->hasTo($setter1->email);
        });
        Mail::assertQueued(CallForPapersMail::class, function ($mail) use ($setter2) {
            return $mail->hasTo($setter2->email);
        });
        $this->assertNotNull(option('date_receive_call_for_papers_email_sent_semester_1'));
    }

    /** @test */
    public function emails_are_sent_for_the_date_receive_call_for_papers_option_when_after_the_correct_day_if_it_has_not_already_been_sent()
    {
        Mail::fake();
        $course1 = create(Course::class);
        $course2 = create(Course::class);
        $setter1 = create(User::class);
        $setter1->markAsSetter($course1);
        $setter1->markAsSetter($course2);
        $setter2 = create(User::class);
        $setter2->markAsSetter($course1);
        $setter2->markAsSetter($course2);
        $moderator = create(User::class);
        $moderator->markAsModerator($course1);

        option(['date_receive_call_for_papers' => now()->subDays(3)->format('Y-m-d')]);
        option(['start_semester_1' => now()->format('Y-m-d')]);
        option(['start_semester_2' => now()->addWeek()->format('Y-m-d')]);
        option(['start_semester_3' => now()->addMonth()->format('Y-m-d')]);

        $this->assertNull(option('date_receive_call_for_papers_email_sent_semester_1'));

        $this->artisan('examdb:timed-notifications');

        Mail::assertQueued(CallForPapersMail::class, 2);
        Mail::assertQueued(CallForPapersMail::class, function ($mail) use ($setter1) {
            return $mail->hasTo($setter1->email);
        });
        Mail::assertQueued(CallForPapersMail::class, function ($mail) use ($setter2) {
            return $mail->hasTo($setter2->email);
        });
        $this->assertNotNull(option('date_receive_call_for_papers_email_sent_semester_1'));
    }

    /** @test */
    public function emails_are_not_sent_for_the_date_receive_call_for_papers_option_when_it_is_not_the_correct_day()
    {
        Mail::fake();
        $course1 = create(Course::class);
        $setter1 = create(User::class);
        $setter1->markAsSetter($course1);
        option(['start_semester_1' => now()->format('Y-m-d')]);
        option(['start_semester_2' => now()->addWeek()->format('Y-m-d')]);
        option(['start_semester_3' => now()->addMonth()->format('Y-m-d')]);

        option(['date_receive_call_for_papers' => now()->addDay()]);

        $this->assertNull(option('date_receive_call_for_papers_email_sent_semester_1'));

        $this->artisan('examdb:timed-notifications');

        Mail::assertNothingQueued();
        $this->assertNull(option('date_receive_call_for_papers_email_sent_semester_1'));
    }

    /** @test */
    public function emails_for_call_for_papers_are_only_sent_about_courses_in_the_current_semester()
    {
        Mail::fake();
        $course1 = create(Course::class, ['semester' => 1]);
        $setter1 = create(User::class);
        $setter1->markAsSetter($course1);
        $course2 = create(Course::class, ['semester' => 2]);
        $setter2 = create(User::class);
        $setter2->markAsSetter($course2);
        // make sure we are in semester 1
        option(['start_semester_1' => now()->format('Y-m-d')]);
        option(['start_semester_2' => now()->addWeek()->format('Y-m-d')]);
        option(['start_semester_3' => now()->addMonth()->format('Y-m-d')]);
        option(['date_receive_call_for_papers' => now()->subDays(3)->format('Y-m-d')]);

        $this->assertNull(option('date_receive_call_for_papers_email_sent_semester_1'));

        $this->artisan('examdb:timed-notifications');

        Mail::assertQueued(CallForPapersMail::class, 1);
        Mail::assertQueued(CallForPapersMail::class, function ($mail) use ($setter1) {
            return $mail->hasTo($setter1->email);
        });
        $this->assertNotNull(option('date_receive_call_for_papers_email_sent_semester_1'));
    }
    /** @test */
    public function emails_are_not_sent_twice_for_the_date_receive_call_for_papers_option_even_when_it_is_the_correct_day()
    {
        Mail::fake();
        $course1 = create(Course::class);
        $setter1 = create(User::class);
        $setter1->markAsSetter($course1);
        option(['start_semester_1' => now()->format('Y-m-d')]);
        option(['start_semester_2' => now()->addWeek()->format('Y-m-d')]);
        option(['start_semester_3' => now()->addMonth()->format('Y-m-d')]);

        option(['date_receive_call_for_papers' => now()->subHour()->format('Y-m-d')]);

        $this->assertNull(option('date_receive_call_for_papers_email_sent'));

        $this->artisan('examdb:timed-notifications');

        Mail::assertQueued(CallForPapersMail::class, 1);
        Mail::assertQueued(CallForPapersMail::class, function ($mail) use ($setter1) {
            return $mail->hasTo($setter1->email);
        });

        Mail::fake();

        $this->artisan('examdb:timed-notifications');
        Mail::assertNothingQueued();
    }

    /** @test */
    public function emails_are_sent_for_the_glasgow_staff_submission_deadline_option_when_it_is_the_week_before_and_day_after()
    {
        Mail::fake();
        $course1 = create(Course::class, ['code' => 'ENG1234']);
        $course2 = create(Course::class, ['code' => 'ENG4567']);
        $course3 = create(Course::class, ['code' => 'UESTC1234']);
        $setter1 = create(User::class);
        $setter1->markAsSetter($course1);
        $setter1->markAsSetter($course2);
        $setter2 = create(User::class);
        $setter2->markAsSetter($course1);
        $setter2->markAsSetter($course2);
        $setter3 = create(User::class);
        $setter3->markAsSetter($course3);
        $moderator = create(User::class);
        $moderator->markAsModerator($course1);
        option(['start_semester_1' => now()->format('Y-m-d')]);
        option(['start_semester_2' => now()->addWeek()->format('Y-m-d')]);
        option(['start_semester_3' => now()->addMonth()->format('Y-m-d')]);

        option(['glasgow_staff_submission_deadline' => now()->addWeek()->format('Y-m-d')]);

        $this->assertNull(option('glasgow_staff_submission_deadline_email_sent'));

        $this->artisan('examdb:timed-notifications');

        Mail::assertQueued(SubmissionDeadlineMail::class, 2);
        Mail::assertQueued(SubmissionDeadlineMail::class, function ($mail) use ($setter1) {
            return $mail->hasTo($setter1->email);
        });
        Mail::assertQueued(SubmissionDeadlineMail::class, function ($mail) use ($setter2) {
            return $mail->hasTo($setter2->email);
        });
        $this->assertNull(option('glasgow_staff_submission_deadline_email_sent'));

        option(['glasgow_staff_submission_deadline' => now()->subDay()->format('Y-m-d')]);

        Mail::fake();

        $this->artisan('examdb:timed-notifications');

        Mail::assertQueued(SubmissionDeadlinePassedMail::class, 2);
        Mail::assertQueued(SubmissionDeadlinePassedMail::class, function ($mail) use ($setter1) {
            return $mail->hasTo($setter1->email);
        });
        Mail::assertQueued(SubmissionDeadlinePassedMail::class, function ($mail) use ($setter2) {
            return $mail->hasTo($setter2->email);
        });
        $this->assertNotNull(option('glasgow_staff_submission_deadline_email_sent'));
    }

    /** @test */
    public function emails_are_sent_for_the_uestc_staff_submission_deadline_option_when_it_is_the_day_before_and_day_after()
    {
        Mail::fake();
        $course1 = create(Course::class, ['code' => 'ENG1234']);
        $course2 = create(Course::class, ['code' => 'ENG4567']);
        $course3 = create(Course::class, ['code' => 'UESTC1234']);
        $setter1 = create(User::class);
        $setter1->markAsSetter($course1);
        $setter1->markAsSetter($course2);
        $setter2 = create(User::class);
        $setter2->markAsSetter($course1);
        $setter2->markAsSetter($course2);
        $setter3 = create(User::class);
        $setter3->markAsSetter($course3);
        $moderator = create(User::class);
        $moderator->markAsModerator($course1);
        option(['start_semester_1' => now()->format('Y-m-d')]);
        option(['start_semester_2' => now()->addWeek()->format('Y-m-d')]);
        option(['start_semester_3' => now()->addMonth()->format('Y-m-d')]);

        option(['uestc_staff_submission_deadline' => now()->addWeek()->format('Y-m-d')]);

        $this->assertNull(option('uestc_staff_submission_deadline_email_sent'));

        $this->artisan('examdb:timed-notifications');

        Mail::assertQueued(SubmissionDeadlineMail::class, 1);
        Mail::assertQueued(SubmissionDeadlineMail::class, function ($mail) use ($setter3) {
            return $mail->hasTo($setter3->email);
        });
        $this->assertNull(option('uestc_staff_submission_deadline_email_sent'));

        option(['uestc_staff_submission_deadline' => now()->subDay()->format('Y-m-d')]);

        Mail::fake();

        $this->artisan('examdb:timed-notifications');

        Mail::assertQueued(SubmissionDeadlinePassedMail::class, 1);
        Mail::assertQueued(SubmissionDeadlinePassedMail::class, function ($mail) use ($setter3) {
            return $mail->hasTo($setter3->email);
        });
        $this->assertNotNull(option('uestc_staff_submission_deadline_email_sent'));
    }

    /** @test */
    public function emails_are_not_sent_for_any_staff_submission_deadline_option_when_it_is_not_the_day_before_or_day_after()
    {
        Mail::fake();
        $course1 = create(Course::class, ['code' => 'ENG1234']);
        $course2 = create(Course::class, ['code' => 'ENG4567']);
        $course3 = create(Course::class, ['code' => 'UESTC1234']);
        $setter1 = create(User::class);
        $setter1->markAsSetter($course1);
        $setter1->markAsSetter($course2);
        $setter2 = create(User::class);
        $setter2->markAsSetter($course1);
        $setter2->markAsSetter($course2);
        $setter3 = create(User::class);
        $setter3->markAsSetter($course3);
        $moderator = create(User::class);
        $moderator->markAsModerator($course1);
        option(['start_semester_1' => now()->format('Y-m-d')]);
        option(['start_semester_2' => now()->addWeek()->format('Y-m-d')]);
        option(['start_semester_3' => now()->addMonth()->format('Y-m-d')]);

        option(['glasgow_staff_submission_deadline' => now()->addDays(10)->format('Y-m-d')]);
        option(['uestc_staff_submission_deadline' => now()->addDays(20)->format('Y-m-d')]);

        $this->assertNull(option('glasgow_staff_submission_deadline_email_sent'));
        $this->assertNull(option('uestc_staff_submission_deadline_email_sent'));

        $this->artisan('examdb:timed-notifications');

        Mail::assertNothingQueued();
        $this->assertNull(option('glasgow_staff_submission_deadline_email_sent'));
        $this->assertNull(option('uestc_staff_submission_deadline_email_sent'));
    }

    /** @test */
    public function emails_are_not_sent_for_any_staff_submission_deadline_option_when_it_is_day_after_but_paperwork_is_complete()
    {
        Mail::fake();
        $course1 = create(Course::class, [
            'code' => 'ENG1234',
        ]);
        $course1->checklists()->save(make(PaperChecklist::class, ['course_id' => $course1->id]));
        $setter1 = create(User::class);
        $setter1->markAsSetter($course1);
        $moderator = create(User::class);
        $moderator->markAsModerator($course1);

        option(['glasgow_staff_submission_deadline' => now()->subDays(1)->format('Y-m-d')]);

        $this->assertNull(option('glasgow_staff_submission_deadline_email_sent'));

        $this->artisan('examdb:timed-notifications');

        Mail::assertNothingQueued();
        $this->assertNotNull(option('glasgow_staff_submission_deadline_email_sent'));
    }

    /** @test */
    public function submission_deadline_emails_are_only_sent_about_the_current_semester()
    {
        Mail::fake();
        $course1 = create(Course::class, ['semester' => 1]);
        $setter1 = create(User::class);
        $setter1->markAsSetter($course1);
        $course2 = create(Course::class, ['semester' => 2]);
        $setter2 = create(User::class);
        $setter2->markAsSetter($course2);

        // make it semester 1 'now'
        option(['start_semester_1' => now()->format('Y-m-d')]);
        option(['start_semester_2' => now()->addWeek()->format('Y-m-d')]);
        option(['start_semester_3' => now()->addMonth()->format('Y-m-d')]);

        option(['glasgow_staff_submission_deadline' => now()->addWeek()->format('Y-m-d')]);

        $this->assertNull(option('glasgow_staff_submission_deadline_email_sent_upcoming_semester_1'));

        $this->artisan('examdb:timed-notifications');

        Mail::assertQueued(SubmissionDeadlineMail::class, 1);
        Mail::assertQueued(SubmissionDeadlineMail::class, function ($mail) use ($setter1) {
            return $mail->hasTo($setter1);
        });
        $this->assertNotNull(option('glasgow_staff_submission_deadline_email_sent_upcoming_semester_1'));

        Mail::fake();

        option(['glasgow_staff_submission_deadline' => now()->subDay()->format('Y-m-d')]);
        $this->assertNull(option('glasgow_staff_submission_deadline_email_sent_reminder_semester_1'));

        $this->artisan('examdb:timed-notifications');

        Mail::assertQueued(SubmissionDeadlinePassedMail::class, 1);
        Mail::assertQueued(SubmissionDeadlinePassedMail::class, function ($mail) use ($setter1) {
            return $mail->hasTo($setter1->email);
        });
        $this->assertNotNull(option('glasgow_staff_submission_deadline_email_sent_reminder_semester_1'));
    }

    /** @test */
    public function emails_are_sent_to_glasgow_staff_about_glasgow_internal_moderation_deadline_three_days_before_and_one_day_after_deadline()
    {
        Mail::fake();
        $course1 = create(Course::class, ['code' => 'ENG1234']);
        $course2 = create(Course::class, ['code' => 'ENG4567']);
        $course3 = create(Course::class, ['code' => 'UESTC1234']);
        $setter1 = create(User::class);
        $setter1->markAsSetter($course1);
        $setter1->markAsSetter($course2);
        $setter2 = create(User::class);
        $setter2->markAsSetter($course1);
        $setter2->markAsSetter($course2);
        $setter3 = create(User::class);
        $setter3->markAsSetter($course3);
        $moderator = create(User::class);
        $moderator->markAsModerator($course1);

        option(['glasgow_internal_moderation_deadline' => now()->addDays(3)->format('Y-m-d')]);

        $this->assertNull(option('glasgow_internal_moderation_deadline_email_sent'));

        $this->artisan('examdb:timed-notifications');

        Mail::assertQueued(ModerationDeadlineMail::class, 1);
        Mail::assertQueued(ModerationDeadlineMail::class, function ($mail) use ($moderator) {
            return $mail->hasTo($moderator->email);
        });
        $this->assertNull(option('glasgow_internal_moderation_deadline_email_sent'));

        option(['glasgow_internal_moderation_deadline' => now()->subDay()->format('Y-m-d')]);

        Mail::fake();

        $this->artisan('examdb:timed-notifications');

        Mail::assertQueued(ModerationDeadlinePassedMail::class, 1);
        Mail::assertQueued(ModerationDeadlinePassedMail::class, function ($mail) use ($moderator) {
            return $mail->hasTo($moderator->email);
        });
        $this->assertNotNull(option('glasgow_internal_moderation_deadline_email_sent'));
    }

    /** @test */
    public function emails_are_not_sent_for_any_staff_moderation_deadline_option_when_it_is_not_the_day_before_or_day_after()
    {
        Mail::fake();
        $course1 = create(Course::class, ['code' => 'ENG1234']);
        $course2 = create(Course::class, ['code' => 'ENG4567']);
        $course3 = create(Course::class, ['code' => 'UESTC1234']);
        $setter1 = create(User::class);
        $setter1->markAsSetter($course1);
        $setter1->markAsSetter($course2);
        $setter2 = create(User::class);
        $setter2->markAsSetter($course1);
        $setter2->markAsSetter($course2);
        $setter3 = create(User::class);
        $setter3->markAsSetter($course3);
        $moderator = create(User::class);
        $moderator->markAsModerator($course1);

        option(['glasgow_internal_moderation_deadline' => now()->addDays(10)->format('Y-m-d')]);
        option(['uesct_internal_moderation_deadline' => now()->addDays(20)->format('Y-m-d')]);

        $this->assertNull(option('glasgow_internal_moderation_deadline_email_sent'));
        $this->assertNull(option('uestc_internal_moderation_deadline_email_sent'));

        $this->artisan('examdb:timed-notifications');

        Mail::assertNothingQueued();
        $this->assertNull(option('glasgow_internal_moderation_deadline_email_sent'));
        $this->assertNull(option('uestc_internal_moderation_deadline_email_sent'));
    }

    /** @test */
    public function emails_are_not_sent_for_any_staff_moderation_deadline_option_when_it_is_day_after_but_paperwork_is_complete()
    {
        Mail::fake();
        $course1 = create(Course::class, [
            'code' => 'ENG1234',
            'moderator_approved_main' => true,
            'moderator_approved_resit' => true,
        ]);
        $setter1 = create(User::class);
        $setter1->markAsSetter($course1);
        $moderator = create(User::class);
        $moderator->markAsModerator($course1);

        option(['glasgow_internal_moderation_deadline' => now()->subDays(1)->format('Y-m-d')]);
        option(['uestc_internal_moderation_deadline' => now()->subDays(1)->format('Y-m-d')]);

        $this->assertNull(option('glasgow_internal_moderation_deadline_email_sent'));
        $this->assertNull(option('uestc_internal_moderation_deadline_email_sent'));

        $this->artisan('examdb:timed-notifications');

        Mail::assertNothingQueued();
        $this->assertNotNull(option('glasgow_internal_moderation_deadline_email_sent'));
        $this->assertNotNull(option('uestc_internal_moderation_deadline_email_sent'));
    }

    /** @test */
    public function staff_moderation_emails_are_only_sent_about_the_current_semester()
    {
        Mail::fake();
        $course1 = create(Course::class, ['semester' => 1]);
        $setter1 = create(User::class);
        $setter1->markAsSetter($course1);
        $moderator1 = create(User::class);
        $moderator1->markAsModerator($course1);
        $course2 = create(Course::class, ['semester' => 2]);
        $setter2 = create(User::class);
        $setter2->markAsSetter($course2);
        $moderator2 = create(User::class);
        $moderator2->markAsModerator($course2);
        // make it semester 1 'now'
        option(['start_semester_1' => now()->format('Y-m-d')]);
        option(['start_semester_2' => now()->addWeek()->format('Y-m-d')]);
        option(['start_semester_3' => now()->addMonth()->format('Y-m-d')]);

        option(['glasgow_internal_moderation_deadline' => now()->addDays(3)->format('Y-m-d')]);
        option(['uestc_internal_moderation_deadline' => now()->addDays(3)->format('Y-m-d')]);

        $this->assertNull(option('glasgow_internal_moderation_deadline_email_sent_upcoming_semester_1'));
        $this->assertNull(option('uestc_internal_moderation_deadline_email_sent_upcoming_semester_1'));

        $this->artisan('examdb:timed-notifications');

        Mail::assertQueued(ModerationDeadlineMail::class, 1);
        Mail::assertQueued(ModerationDeadlineMail::class, function ($mail) use ($moderator1) {
            return $mail->hasTo($moderator1->email);
        });
        $this->assertNotNull(option('glasgow_internal_moderation_deadline_email_sent_upcoming_semester_1'));
        $this->assertNotNull(option('uestc_internal_moderation_deadline_email_sent_upcoming_semester_1'));

        Mail::fake();

        option(['glasgow_internal_moderation_deadline' => now()->subDays(1)->format('Y-m-d')]);
        option(['uestc_internal_moderation_deadline' => now()->subDays(1)->format('Y-m-d')]);

        $this->assertNull(option('glasgow_internal_moderation_deadline_email_sent_reminder_semester_1'));
        $this->assertNull(option('uestc_internal_moderation_deadline_email_sent_reminder_semester_1'));

        $this->artisan('examdb:timed-notifications');

        Mail::assertQueued(ModerationDeadlinePassedMail::class, 1);
        Mail::assertQueued(ModerationDeadlinePassedMail::class, function ($mail) use ($moderator1) {
            return $mail->hasTo($moderator1->email);
        });
        $this->assertNotNull(option('glasgow_internal_moderation_deadline_email_sent_reminder_semester_1'));
        $this->assertNotNull(option('uestc_internal_moderation_deadline_email_sent_reminder_semester_1'));
    }

    /** @test */
    public function email_is_sent_to_glasgow_teaching_office_about_notifying_externals()
    {
        Mail::fake();

        option(['date_remind_glasgow_office_externals' => now()->format('Y-m-d')]);
        option(['teaching_office_contact_glasgow' => 'glasgow@example.com']);

        $this->assertNull(option('date_remind_glasgow_office_externals_email_sent'));

        $this->artisan('examdb:timed-notifications');

        Mail::assertQueued(NotifyExternalsReminderMail::class, 1);
        Mail::assertQueued(NotifyExternalsReminderMail::class, function ($mail) {
            return $mail->hasTo('glasgow@example.com');
        });
        $this->assertNotNull(option('date_remind_glasgow_office_externals_email_sent'));
    }

    /** @test */
    public function email_is_sent_to_uestc_teaching_office_about_notifying_externals()
    {
        Mail::fake();

        option(['date_remind_uestc_office_externals' => now()->format('Y-m-d')]);
        option(['teaching_office_contact_uestc' => 'uestc@example.com']);

        $this->assertNull(option('date_remind_uestc_office_externals_email_sent'));

        $this->artisan('examdb:timed-notifications');

        Mail::assertQueued(NotifyExternalsReminderMail::class, 1);
        Mail::assertQueued(NotifyExternalsReminderMail::class, function ($mail) {
            return $mail->hasTo('uestc@example.com');
        });
        $this->assertNotNull(option('date_remind_uestc_office_externals_email_sent'));
    }

    /** @test */
    public function email_is_not_sent_to_any_teaching_office_about_notifying_externals_if_it_is_not_the_right_day()
    {
        Mail::fake();

        option(['date_remind_uestc_office_externals' => now()->addDays(10)->format('Y-m-d')]);
        option(['teaching_office_contact_uestc' => 'uestc@example.com']);

        $this->assertNull(option('date_remind_uestc_office_externals_email_sent'));

        $this->artisan('examdb:timed-notifications');

        Mail::assertNothingQueued();
        $this->assertNull(option('date_remind_uestc_office_externals_email_sent'));
    }

    /** @test */
    public function emails_are_sent_to_the_glasgow_teaching_office_one_day_before_and_one_day_after_the_print_deadline()
    {
        Mail::fake();

        option(['glasgow_print_ready_deadline' => now()->addDay()->format('Y-m-d')]);
        option(['teaching_office_contact_glasgow' => 'glasgow@example.com']);

        $this->assertNull(option('glasgow_print_ready_deadline_email_sent'));

        $this->artisan('examdb:timed-notifications');

        Mail::assertQueued(PrintReadyDeadlineMail::class, 1);
        Mail::assertQueued(PrintReadyDeadlineMail::class, function ($mail) {
            return $mail->hasTo('glasgow@example.com');
        });
        $this->assertNull(option('glasgow_print_ready_deadline_email_sent'));

        Mail::fake();
        option(['glasgow_print_ready_deadline' => now()->subDay()->format('Y-m-d')]);

        $this->artisan('examdb:timed-notifications');

        Mail::assertQueued(PrintReadyDeadlinePassedMail::class, 1);
        Mail::assertQueued(PrintReadyDeadlinePassedMail::class, function ($mail) {
            return $mail->hasTo('glasgow@example.com');
        });
        $this->assertNotNull(option('glasgow_print_ready_deadline_email_sent'));
    }

    /** @test */
    public function emails_are_sent_to_the_uestc_teaching_office_one_day_before_and_one_day_after_the_print_deadline()
    {
        Mail::fake();

        option(['uestc_print_ready_deadline' => now()->addDay()->format('Y-m-d')]);
        option(['teaching_office_contact_uestc' => 'uestc@example.com']);

        $this->assertNull(option('uestc_print_ready_deadline_email_sent'));

        $this->artisan('examdb:timed-notifications');

        Mail::assertQueued(PrintReadyDeadlineMail::class, 1);
        Mail::assertQueued(PrintReadyDeadlineMail::class, function ($mail) {
            return $mail->hasTo('uestc@example.com');
        });
        $this->assertNull(option('uestc_print_ready_deadline_email_sent'));

        Mail::fake();
        option(['uestc_print_ready_deadline' => now()->subDay()->format('Y-m-d')]);

        $this->artisan('examdb:timed-notifications');

        Mail::assertQueued(PrintReadyDeadlinePassedMail::class, 1);
        Mail::assertQueued(PrintReadyDeadlinePassedMail::class, function ($mail) {
            return $mail->hasTo('uestc@example.com');
        });
        $this->assertNotNull(option('uestc_print_ready_deadline_email_sent'));
    }

    /** @test */
    public function emails_are_not_sent_to_the_teaching_office_about_the_print_deadline_if_it_is_not_one_day_before_or_after_the_deadline()
    {
        Mail::fake();

        option(['uestc_print_ready_deadline' => now()->addDays(33)->format('Y-m-d')]);
        option(['teaching_office_contact_uestc' => 'uestc@example.com']);
        option(['glasgow_print_ready_deadline' => now()->addDays(33)->format('Y-m-d')]);
        option(['teaching_office_contact_glasgow' => 'glasgow@example.com']);

        $this->assertNull(option('uestc_print_ready_deadline_email_sent'));
        $this->assertNull(option('glasgow_print_ready_deadline_email_sent'));

        $this->artisan('examdb:timed-notifications');

        Mail::assertNothingQueued();
        $this->assertNull(option('uestc_print_ready_deadline_email_sent'));
        $this->assertNull(option('glasgow_print_ready_deadline_email_sent'));
    }

    /** @test */
    public function emails_about_the_print_deadline_passing_are_only_sent_once()
    {
        Mail::fake();

        option(['glasgow_print_ready_deadline' => now()->subDay()->format('Y-m-d')]);
        option(['teaching_office_contact_glasgow' => 'glasgow@example.com']);

        $this->artisan('examdb:timed-notifications');
        $this->artisan('examdb:timed-notifications');

        Mail::assertQueued(PrintReadyDeadlinePassedMail::class, 1);
        Mail::assertQueued(PrintReadyDeadlinePassedMail::class, function ($mail) {
            return $mail->hasTo('glasgow@example.com');
        });
        $this->assertNotNull(option('glasgow_print_ready_deadline_email_sent'));
    }

    /** @test */
    public function email_is_sent_to_glasgow_teaching_office_about_externals_deadline()
    {
        Mail::fake();

        option(['glasgow_external_moderation_deadline' => now()->format('Y-m-d')]);
        option(['teaching_office_contact_glasgow' => 'glasgow@example.com']);

        $this->assertNull(option('glasgow_external_moderation_deadline_email_sent'));

        $this->artisan('examdb:timed-notifications');

        Mail::assertQueued(ExternalModerationDeadlineMail::class, 1);
        Mail::assertQueued(ExternalModerationDeadlineMail::class, function ($mail) {
            return $mail->hasTo('glasgow@example.com');
        });
        $this->assertNotNull(option('glasgow_external_moderation_deadline_email_sent'));
    }

    /** @test */
    public function email_is_sent_to_uestc_teaching_office_about_externals_deadline()
    {
        Mail::fake();

        option(['uestc_external_moderation_deadline' => now()->format('Y-m-d')]);
        option(['teaching_office_contact_uestc' => 'uestc@example.com']);

        $this->assertNull(option('uestc_external_moderation_deadline_email_sent'));

        $this->artisan('examdb:timed-notifications');

        Mail::assertQueued(ExternalModerationDeadlineMail::class, 1);
        Mail::assertQueued(ExternalModerationDeadlineMail::class, function ($mail) {
            return $mail->hasTo('uestc@example.com');
        });
        $this->assertNotNull(option('uestc_external_moderation_deadline_email_sent'));
    }

    /** @test */
    public function email_are_not_sent_to_teaching_office_about_externals_deadline_if_not_the_right_day()
    {
        Mail::fake();

        option(['uestc_external_moderation_deadline' => now()->addDays(14)->format('Y-m-d')]);
        option(['teaching_office_contact_uestc' => 'uestc@example.com']);

        $this->assertNull(option('uestc_external_moderation_deadline_email_sent'));

        $this->artisan('examdb:timed-notifications');

        Mail::assertNothingQueued(ExternalModerationDeadlineMail::class, 1);
        $this->assertNull(option('uestc_external_moderation_deadline_email_sent'));
    }

    /** @test */
    public function if_something_goes_wrong_sending_a_notification_we_get_a_timed_notification_exception()
    {
        $this->expectException(TimedNotificationException::class);

        option(['uestc_external_moderation_deadline' => now()->format('Y-m-d')]);
        option(['teaching_office_contact_uestc' => 44]);

        $this->artisan('examdb:timed-notifications');

        $this->assertNothingQueued();
    }
}
