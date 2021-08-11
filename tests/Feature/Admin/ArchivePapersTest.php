<?php

namespace Tests\Feature\Admin;

use App\User;
use App\Paper;
use App\Course;
use Tests\TestCase;
use App\PaperChecklist;
use App\AcademicSession;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ArchivePapersTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        AcademicSession::createFirstSession();
    }

    /** @test */
    public function admins_can_archive_the_papers_for_a_single_course()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $course = create(Course::class, ['moderator_approved_resit' => true]);
        $paper1 = create(Paper::class, ['course_id' => $course->id]);
        $paper2 = create(Paper::class);
        $checklist1 = create(PaperChecklist::class, ['course_id' => $course->id]);
        $checklist2 = create(PaperChecklist::class);

        $this->assertTrue($course->fresh()->isApprovedByModerator('resit'));

        $response = $this->actingAs($admin)->post(route('course.papers.archive', $course->id));

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        $this->assertTrue($paper1->fresh()->isArchived());
        $this->assertEquals(now()->format('d-m-Y'), $paper1->fresh()->archived_at->format('d-m-Y'));
        $this->assertFalse($paper2->fresh()->isArchived());
        $this->assertTrue($checklist1->fresh()->isArchived());
        $this->assertEquals(now()->format('d-m-Y'), $checklist1->fresh()->archived_at->format('d-m-Y'));
        $this->assertFalse($checklist2->fresh()->isArchived());
        $this->assertFalse($course->fresh()->isApprovedByModerator('resit'));
    }

    /** @test */
    public function admins_can_archive_the_papers_for_a_whole_area()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $user1 = create(User::class);
        $course1 = create(Course::class, ['code' => 'ENG1234', 'moderator_approved_main' => true]);
        $course2 = create(Course::class, ['code' => 'ENG4567', 'external_approved_resit' => true]);
        $course3 = create(Course::class, ['code' => 'UESTC4567', 'moderator_approved_main' => true]);
        $paper1 = create(Paper::class, ['course_id' => $course1->id]);
        $paper2 = create(Paper::class, ['course_id' => $course2->id]);
        $paper3 = create(Paper::class, ['course_id' => $course3->id]);
        $checklist1 = create(PaperChecklist::class, ['course_id' => $course1->id]);
        $checklist2 = create(PaperChecklist::class, ['course_id' => $course2->id]);
        $checklist3 = create(PaperChecklist::class, ['course_id' => $course3->id]);

        $this->assertTrue($course1->fresh()->isApprovedByModerator('main'));
        $this->assertTrue($course2->fresh()->isApprovedByExternal('resit'));
        $this->assertTrue($course3->fresh()->isApprovedByModerator('main'));

        $response = $this->actingAs($admin)->post(route('area.papers.archive'), [
            'area' => 'glasgow',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        $this->assertTrue($paper1->fresh()->isArchived());
        $this->assertTrue($paper2->fresh()->isArchived());
        $this->assertFalse($paper3->fresh()->isArchived());
        $this->assertTrue($checklist1->fresh()->isArchived());
        $this->assertTrue($checklist2->fresh()->isArchived());
        $this->assertFalse($checklist3->fresh()->isArchived());
        $this->assertFalse($course1->fresh()->isApprovedByModerator('main'));
        $this->assertFalse($course2->fresh()->isApprovedByExternal('resit'));
        $this->assertTrue($course3->fresh()->isApprovedByModerator('main'));
    }

    /** @test */
    public function admins_view_the_archives_of_all_papers()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $paper1 = create(Paper::class);
        $paper2 = create(Paper::class);
        $paper3 = create(Paper::class);
        $commentPaper = create(Paper::class, ['subcategory' => Paper::COMMENT_SUBCATEGORY]);

        $paper1->archive();
        $paper3->archive();
        $commentPaper->archive();

        $response = $this->actingAs($admin)->get(route('archive.index'));

        $response->assertStatus(200);
        $this->assertTrue($response->data('papers')->contains($paper1));
        $this->assertFalse($response->data('papers')->contains($paper2));
        $this->assertTrue($response->data('papers')->contains($paper3));
        // archived comments don't show up
        $this->assertFalse($response->data('papers')->contains($commentPaper));
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
        $commentPaper = create(Paper::class, ['course_id' => $course->id, 'subcategory' => Paper::COMMENT_SUBCATEGORY]);

        $paper1->archive();
        $paper3->archive();
        $commentPaper->archive();

        $this->assertEquals(3, $course->archivedPapers()->count());
        $this->assertTrue($course->archivedPapers->contains($paper1));
        $this->assertTrue($course->archivedPapers->contains($paper3));
        $this->assertTrue($course->archivedPapers->contains($commentPaper));
    }
}
