<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Paper;
use App\Models\Course;
use App\Models\AcademicSession;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
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
        $course = create(Course::class);
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
    }

    /** @test */
    public function a_setter_can_mark_the_print_ready_paper_as_not_approved()
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
        $this->assertFalse($coursePaper->fresh()->print_ready_approved);

        $response = $this->actingAs($external)->post(route('paper.approve_print_ready', $coursePaper->id), [
            'is_approved' => true,
            'comment' => 'This is a comment',
        ]);

        $response->assertUnauthorized();
        $this->assertFalse($coursePaper->fresh()->print_ready_approved);
    }
}
