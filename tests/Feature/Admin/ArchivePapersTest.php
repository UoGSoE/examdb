<?php

namespace Tests\Feature\Admin;

use App\User;
use App\Paper;
use App\Course;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ArchivePapersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admins_can_archive_the_papers_for_a_single_course()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $course = create(Course::class);
        $paper1 = create(Paper::class, ['course_id' => $course->id]);
        $paper2 = create(Paper::class);

        $response = $this->actingAs($admin)->post(route('course.papers.archive', $course->id));

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        $this->assertTrue($paper1->fresh()->isArchived());
        $this->assertEquals(now()->format('d-m-Y'), $paper1->fresh()->archived_at->format('d-m-Y'));
        $this->assertFalse($paper2->fresh()->isArchived());
    }

    /** @test */
    public function admins_can_archive_the_papers_for_a_whole_area()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $course1 = create(Course::class, ['code' => 'ENG1234']);
        $course2 = create(Course::class, ['code' => 'ENG4567']);
        $course3 = create(Course::class, ['code' => 'UESTC4567']);
        $paper1 = create(Paper::class, ['course_id' => $course1->id]);
        $paper2 = create(Paper::class, ['course_id' => $course2->id]);
        $paper3 = create(Paper::class, ['course_id' => $course3->id]);

        $response = $this->actingAs($admin)->post(route('area.papers.archive'), [
            'area' => 'glasgow',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        $this->assertTrue($paper1->fresh()->isArchived());
        $this->assertTrue($paper2->fresh()->isArchived());
        $this->assertFalse($paper3->fresh()->isArchived());
    }

    /** @test */
    public function admins_view_the_archives_of_all_papers()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $paper1 = create(Paper::class);
        $paper2 = create(Paper::class);
        $paper3 = create(Paper::class);

        $paper1->archive();
        $paper3->archive();

        $response = $this->actingAs($admin)->get(route('archive.index'));

        $response->assertStatus(200);
        $this->assertTrue($response->data('papers')->contains($paper1));
        $this->assertFalse($response->data('papers')->contains($paper2));
        $this->assertTrue($response->data('papers')->contains($paper3));
    }

    /** @test */
    public function we_can_get_a_list_of_just_archived_papers_for_a_course()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $course = create(Course::class);
        $paper1 = create(Paper::class, ['course_id' => $course->id]);
        $paper2 = create(Paper::class, ['course_id' => $course->id]);
        $paper3 = create(Paper::class, ['course_id' => $course->id]);

        $paper1->archive();
        $paper3->archive();

        $this->assertEquals(2, $course->archivedPapers()->count());
        $this->assertTrue($course->archivedPapers->contains($paper1));
        $this->assertTrue($course->archivedPapers->contains($paper3));
    }
}
