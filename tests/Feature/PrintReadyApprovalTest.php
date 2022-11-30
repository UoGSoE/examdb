<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Paper;
use App\Models\Course;
use App\Models\Discipline;
use App\Models\AcademicSession;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\PrintReadyPaperApprovedMail;
use App\Mail\PrintReadyPaperRejectedMail;
use App\Mail\PrintReadyPaperReminderMail;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PrintReadyApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        AcademicSession::createFirstSession();
    }

    /** @test */
    public function if_the_print_ready_paper_has_been_uploaded_setters_see_an_approval_button()
    {
        $this->markTestSkipped('This is all done via vue so we need to test it there... one day');
        Storage::fake('exampapers');
        Mail::fake();
        $admin = User::factory()->admin()->create();
        $setter = create(User::class);
        $course = create(Course::class);
        $paper = UploadedFile::fake()->create('paper.pdf', 1000);
        login($admin);
        $course->addPaper('main', Paper::ADMIN_PRINT_READY_VERSION, $paper);

        $this->actingAs($setter)
            ->get(route('course.show', $course))
            ->assertSee('Approve Print Ready Paper?');
    }

    /** @test */
    public function a_setter_can_mark_the_print_ready_paper_as_approved()
    {
        Storage::fake('exampapers');
        Mail::fake();
        $admin = User::factory()->admin()->create();
        $setter = create(User::class);
        $discipline = create(Discipline::class, ['contact' => 'admin@example.com']);
        $course = create(Course::class, ['discipline_id' => $discipline->id]);
        $setter->markAsSetter($course);
        $paper = UploadedFile::fake()->create('paper.pdf', 1000);
        login($admin);
        $coursePaper = $course->addPaper('main', Paper::ADMIN_PRINT_READY_VERSION, $paper);

        $response = $this->actingAs($setter)->post(route('paper.approve_print_ready', $coursePaper->id), [
            'is_approved' => true,
            'comment' => 'This is a comment',
        ]);

        $response->assertOk();
        $response->assertJson([
            'papers' => [
                'main' => [
                    [
                        'print_ready_approved' => true,
                        'print_ready_comment' => 'This is a comment',
                    ],
                ],
            ],
        ]);
        Mail::assertQueued(PrintReadyPaperApprovedMail::class, 1);
        Mail::assertQueued(PrintReadyPaperApprovedMail::class, function ($mail) use ($discipline, $course) {
            return $mail->hasTo($discipline->contact) && $mail->course->is($course);
        });
    }

    /** @test */
    public function a_setter_can_mark_the_print_ready_paper_as_not_approved()
    {
        Storage::fake('exampapers');
        Mail::fake();
        $admin = User::factory()->admin()->create();
        $setter = create(User::class);
        $discipline = create(Discipline::class, ['contact' => 'admin@example.com']);
        $course = create(Course::class, ['discipline_id' => $discipline->id]);
        $setter->markAsSetter($course);
        $paper = UploadedFile::fake()->create('paper.pdf', 1000);
        login($admin);
        $coursePaper = $course->addPaper('main', Paper::ADMIN_PRINT_READY_VERSION, $paper);

        $response = $this->actingAs($setter)->post(route('paper.approve_print_ready', $coursePaper->id), [
            'is_approved' => false,
            'comment' => 'This is another comment',
        ]);

        $response->assertOk();
        $response->assertJson([
            'papers' => [
                'main' => [
                    [
                        'print_ready_approved' => false,
                        'print_ready_comment' => 'This is another comment',
                    ],
                ],
            ],
        ]);
        Mail::assertQueued(PrintReadyPaperRejectedMail::class, 1);
        Mail::assertQueued(PrintReadyPaperRejectedMail::class, function ($mail) use ($discipline, $course) {
            return $mail->hasTo($discipline->contact) &&
                $mail->course->is($course) &&
                $mail->reason === 'This is another comment';
        });
    }

    /** @test */
    public function if_a_setter_marks_the_paper_as_not_approved_they_must_provide_a_comment()
    {
        Storage::fake('exampapers');
        Mail::fake();
        $admin = User::factory()->admin()->create();
        $setter = create(User::class);
        $course = create(Course::class);
        $setter->markAsSetter($course);
        $paper = UploadedFile::fake()->create('paper.pdf', 1000);
        login($admin);
        $coursePaper = $course->addPaper('main', Paper::ADMIN_PRINT_READY_VERSION, $paper);

        $response = $this->actingAs($setter)->postJson(route('paper.approve_print_ready', $coursePaper->id), [
            'is_approved' => false,
            'comment' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrorFor('comment');
    }

    /** @test */
    public function if_a_setter_marks_the_paper_as_approved_they_dont_need_to_provide_a_comment()
    {
        Storage::fake('exampapers');
        Mail::fake();
        $admin = User::factory()->admin()->create();
        $setter = create(User::class);
        $course = create(Course::class);
        $setter->markAsSetter($course);
        $paper = UploadedFile::fake()->create('paper.pdf', 1000);
        login($admin);
        $coursePaper = $course->addPaper('main', Paper::ADMIN_PRINT_READY_VERSION, $paper);

        $response = $this->actingAs($setter)->postJson(route('paper.approve_print_ready', $coursePaper->id), [
            'is_approved' => true,
            'comment' => '',
        ]);

        $response->assertOk();
        $response->assertJson([
            'papers' => [
                'main' => [
                    [
                        'print_ready_approved' => true,
                        'print_ready_comment' => '',
                    ],
                ],
            ],
        ]);
    }

    /** @test */
    public function moderators_and_externals_cant_mark_the_print_ready_paper_as_approved_or_not()
    {
        Storage::fake('exampapers');
        Mail::fake();
        $admin = User::factory()->admin()->create();
        $setter = create(User::class);
        $moderator = create(User::class);
        $external = create(User::class);
        $course = create(Course::class);
        $setter->markAsSetter($course);
        $moderator->markAsModerator($course);
        $external->markAsExternal($course);
        $paper = UploadedFile::fake()->create('paper.pdf', 1000);
        login($admin);
        $coursePaper = $course->addPaper('main', Paper::ADMIN_PRINT_READY_VERSION, $paper);

        $response = $this->actingAs($moderator)->post(route('paper.approve_print_ready', $coursePaper->id), [
            'is_approved' => true,
            'comment' => 'This is a comment',
        ]);

        $response->assertUnauthorized();
        $this->assertNull($coursePaper->fresh()->print_ready_approved);

        $response = $this->actingAs($external)->post(route('paper.approve_print_ready', $coursePaper->id), [
            'is_approved' => true,
            'comment' => 'This is a comment',
        ]);

        $response->assertUnauthorized();
        $this->assertNull($coursePaper->fresh()->print_ready_approved);
    }

    /** @test */
    public function staff_who_havent_approved_or_rejected_the_print_ready_papers_after_48hrs_get_a_reminder_email()
    {
        Storage::fake('exampapers');
        Mail::fake();
        $admin = User::factory()->admin()->create();
        $setter1 = create(User::class);
        $setter2 = create(User::class);
        $discipline = create(Discipline::class, ['contact' => 'admin@example.com']);
        $course1 = create(Course::class, ['discipline_id' => $discipline->id]);
        $setter1->markAsSetter($course1);
        $course2 = create(Course::class, ['discipline_id' => $discipline->id]);
        $setter2->markAsSetter($course2);
        $course3 = create(Course::class, ['discipline_id' => $discipline->id]);
        $setter2->markAsSetter($course3);
        $newPaper = Paper::factory()->create([
            'subcategory' => Paper::ADMIN_PRINT_READY_VERSION,
            'course_id' => $course1->id,
            'created_at' => now()->subHours(3)
        ]);
        $oldPaper = Paper::factory()->create([
            'subcategory' => Paper::ADMIN_PRINT_READY_VERSION,
            'course_id' => $course2->id,
            'created_at' => now()->subHours(49)
        ]);
        $oldPaperAlreadyRemindedAbout = Paper::factory()->create([
            'subcategory' => Paper::ADMIN_PRINT_READY_VERSION,
            'course_id' => $course3->id,
            'created_at' => now()->subHours(49),
            'print_ready_reminder_sent' => now()->subHours(1)->format('Y-m-d H:i:s'),
        ]);

        $this->artisan('examdb:send-print-ready-reminder-emails');

        Mail::assertQueued(PrintReadyPaperReminderMail::class, 1);
        Mail::assertQueued(PrintReadyPaperReminderMail::class, function ($mail) use ($setter2, $course2) {
            return $mail->hasTo($setter2->email) &&
                $mail->courseCodes->count() === 1 &&
                $mail->courseCodes->contains($course2->code);
        });
        $this->assertNull($newPaper->fresh()->print_ready_reminder_sent);
        $this->assertNotNull($oldPaper->fresh()->print_ready_reminder_sent);
        $this->assertNotNull($oldPaperAlreadyRemindedAbout->fresh()->print_ready_reminder_sent);
    }

    /** @test */
    public function the_reminder_command_is_enabled_in_the_schedular()
    {
        $this->assertCommandIsScheduled('examdb:send-print-ready-reminder-emails');
    }
}
