<?php

namespace Tests\Feature;

use App\AcademicSession;
use App\Course;
use App\Discipline;
use App\Mail\PaperForRegistryUploaded;
use App\Paper;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaperForRegistryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        AcademicSession::createFirstSession();
    }

    /** @test */
    public function when_an_admin_uploads_the_paper_for_registy_an_email_is_sent_to_the_setters()
    {
        $this->withoutExceptionHandling();
        Mail::fake();
        Storage::fake('exampapers');

        $discipline = create(Discipline::class, ['contact' => 'someone@example.com']);
        $admin = User::factory()->admin()->create();
        $setter1 = User::factory()->create();
        $setter2 = User::factory()->create();
        $moderator = User::factory()->create();
        $course = Course::factory()->create(['discipline_id' => $discipline->id]);
        $setter1->markAsSetter($course);
        $setter2->markAsSetter($course);
        $moderator->markAsModerator($course);
        $file = UploadedFile::fake()->create('paper_for_registry.pdf', 1);

        $response = $this->actingAs($admin)->postJson(route('course.paper.store', $course->id), [
            'paper' => $file,
            'category' => 'main',
            'subcategory' => Paper::PAPER_FOR_REGISTRY,
            'comment' => 'Whatever',
        ]);

        $response->assertSuccessful();
        Mail::assertQueued(PaperForRegistryUploaded::class, 2);
        Mail::assertQueued(PaperForRegistryUploaded::class, function ($mail) use ($setter1) {
            return $mail->hasTo($setter1->email);
        });
        Mail::assertQueued(PaperForRegistryUploaded::class, function ($mail) use ($setter2) {
            return $mail->hasTo($setter2->email);
        });
    }

    /** @test */
    public function a_setter_can_mark_the_paper_for_registry_as_approved()
    {
        $this->withoutExceptionHandling();
        Mail::fake();
        Storage::fake('exampapers');
        $setter = create(User::class);
        $course = create(Course::class);
        $setter->markAsSetter($course);
        login($setter);
        $course->addPaper('main', Paper::PAPER_FOR_REGISTRY, UploadedFile::fake()->create('document1.pdf', 1));

        $this->assertFalse($course->paperForRegistryIsApproved('main'));
        $response = $this->actingAs($setter)->postJson(route('registry.approve', $course->id), [
            'category' => 'main',
        ]);

        $response->assertOk();
        $this->assertTrue($course->fresh()->paperForRegistryIsApproved('main'));
    }
}
