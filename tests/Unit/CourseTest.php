<?php

namespace Tests\Unit;

use App\AcademicSession;
use App\Course;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        AcademicSession::createFirstSession();
    }

    /** @test */
    public function a_course_can_have_many_main_papers_added()
    {
        Storage::fake('exampapers');
        $user = create(User::class);
        login($user);
        $course = create(Course::class);

        $course->addPaper('main', 'blah de blah', UploadedFile::fake()->create('main_paper_1.pdf'));
        $course->addPaper('main', 'something', UploadedFile::fake()->create('main_paper_2.pdf'));

        $this->assertCount(2, $course->mainPapers);
        Storage::disk('exampapers')->assertExists($course->mainPapers[0]->filename);
        Storage::disk('exampapers')->assertExists($course->mainPapers[1]->filename);
    }

    /** @test */
    public function a_course_can_have_many_resit_papers_added()
    {
        Storage::fake('exampapers');
        $user = create(User::class);
        login($user);
        $course = create(Course::class);

        $course->addPaper('resit', 'something or other', UploadedFile::fake()->create('resit_paper_1.pdf'));
        $course->addPaper('resit', 'some other thing', UploadedFile::fake()->create('resit_paper_2.pdf'));

        $this->assertCount(2, $course->resitPapers);
        Storage::disk('exampapers')->assertExists($course->resitPapers[0]->filename);
        Storage::disk('exampapers')->assertExists($course->resitPapers[1]->filename);
    }
}
