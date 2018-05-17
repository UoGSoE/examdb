<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use App\Course;
use Illuminate\Http\UploadedFile;
use App\User;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_course_can_have_many_main_papers_added()
    {
        Storage::fake('exampapers');
        $user = create(User::class);
        login($user);
        $course = create(Course::class);

        $course->addPaper('main', UploadedFile::fake()->create('main_paper_1.pdf', 1));
        $course->addPaper('main', UploadedFile::fake()->create('main_paper_2.pdf', 1));

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

        $course->addPaper('resit', UploadedFile::fake()->create('resit_paper_1.pdf', 1));
        $course->addPaper('resit', UploadedFile::fake()->create('resit_paper_2.pdf', 1));

        $this->assertCount(2, $course->resitPapers);
        Storage::disk('exampapers')->assertExists($course->resitPapers[0]->filename);
        Storage::disk('exampapers')->assertExists($course->resitPapers[1]->filename);
    }
}
