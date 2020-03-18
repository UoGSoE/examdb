<?php

namespace Tests\Unit;

use App\Course;
use App\Jobs\NotifyExternals;
use App\Mail\ExternalHasPapersToLookAt;
use App\Paper;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ExternalsNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function externals_are_notified_about_any_courses_with_papers()
    {
        Mail::fake();
        $external1 = create(User::class);
        $external2 = create(User::class);
        $external3 = create(User::class); // just to check this external isn't notified
        $paper1 = create(Paper::class);
        $external1->markAsExternal($paper1->course);
        $external2->markAsExternal($paper1->course);

        NotifyExternals::dispatch('glasgow');

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
        $paper1 = create(Paper::class);
        $external1 = create(User::class);
        $external1->markAsExternal($paper1->course);

        Mail::fake();

        NotifyExternals::dispatch('glasgow');

        Mail::assertQueued(ExternalHasPapersToLookAt::class);

        Mail::fake();

        NotifyExternals::dispatch('glasgow');

        Mail::assertNotQueued(ExternalHasPapersToLookAt::class);
    }

    /** @test */
    public function externals_are_only_sent_one_email_even_if_they_are_on_multiple_courses()
    {
        $this->withoutExceptionHandling();
        $paper1 = create(Paper::class);
        $paper2 = create(Paper::class);
        $external1 = create(User::class);
        $external1->markAsExternal($paper1->course);
        $external1->markAsExternal($paper2->course);

        Mail::fake();

        NotifyExternals::dispatch('glasgow');

        Mail::assertQueued(ExternalHasPapersToLookAt::class, 1);
    }

    /** @test */
    public function externals_are_not_notified_about_papers_for_a_different_area_deadline()
    {
        $this->withoutExceptionHandling();
        $glasgowCourse = create(Course::class, ['code' => 'ENG1234']);
        $uestcCourse = create(Course::class, ['code' => 'UESTC1234']);
        $paper1 = create(Paper::class, ['course_id' => $glasgowCourse->id]);
        $paper2 = create(Paper::class, ['course_id' => $uestcCourse->id]);
        $external1 = create(User::class);
        $external2 = create(User::class);
        $external1->markAsExternal($paper1->course);
        $external2->markAsExternal($paper2->course);

        Mail::fake();

        NotifyExternals::dispatch('uestc');

        Mail::assertQueued(ExternalHasPapersToLookAt::class, 1);
    }

    /** @test */
    public function externals_are_not_notified_about_courses_with_no_papers()
    {
        $this->withoutExceptionHandling();
        $course = create(Course::class);
        $external1 = create(User::class);
        $external1->markAsExternal($course);

        Mail::fake();

        NotifyExternals::dispatch('glasgow');

        Mail::assertNotQueued(ExternalHasPapersToLookAt::class);
    }

    /** @test */
    public function courses_where_the_externals_have_been_notified_are_marked_as_such()
    {
        $this->withoutExceptionHandling();
        $paper1 = create(Paper::class);
        $external1 = create(User::class);
        $external1->markAsExternal($paper1->course);

        Mail::fake();

        $this->assertFalse($paper1->course->fresh()->externalNotified());

        NotifyExternals::dispatch('glasgow');

        $this->assertTrue($paper1->course->fresh()->externalNotified());
    }
}
