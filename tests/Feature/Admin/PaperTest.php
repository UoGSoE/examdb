<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\Discipline;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PaperTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        AcademicSession::createFirstSession();
    }

    /** @test */
    public function admins_can_see_all_the_paper_statuses_for_all_the_courses()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $course1 = create(Course::class);
        $course2 = create(Course::class);

        $response = $this->actingAs($admin)->get(route('paper.index'));

        $response->assertOk();
        $response->assertSee('Exam Paper List');
        $response->assertSee($course1->code);
        $response->assertSee($course2->code);
        $response->assertViewHas('courses');
        $response->assertViewHas('disciplines');
        $response->assertViewHas('disciplineFilter');
    }

    /** @test */
    public function admins_can_see_all_the_paper_statuses_for_all_the_courses_on_a_specifc_discipline()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $discipline1 = create(Discipline::class);
        $discipline2 = create(Discipline::class);
        $course1 = create(Course::class, ['discipline_id' => $discipline1->id]);
        $course2 = create(Course::class, ['discipline_id' => $discipline2->id]);

        $response = $this->actingAs($admin)->get(route('paper.index'));

        $response->assertOk();
        $response->assertSeeLivewire('paper-report');

        Livewire::actingAs($admin)->test('paper-report')
            ->assertSee($course1->code)
            ->assertSee($course2->code)
            ->set('disciplineFilter', $discipline1->id)
            ->assertSee($course1->code)
            ->assertDontSee($course2->code)
            ->set('disciplineFilter', $discipline2->id)
            ->assertDontSee($course1->code)
            ->assertSee($course2->code);
    }

    /** @test */
    public function admins_can_export_the_list_of_papers()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $course1 = create(Course::class);
        $course2 = create(Course::class);

        $response = $this->actingAs($admin)->get(route('admin.paper.export'));

        $header = $response->headers->get('content-disposition');
        $this->assertEquals($header, 'attachment; filename=examdb_papers_'.now()->format('d_m_Y_H_i').'.xlsx');
    }

    /** @test */
    public function admins_can_see_all_the_correct_information_about_the_print_ready_paper_status()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $course1 = create(Course::class);
        $course2 = create(Course::class);
        $printReadyPaper = create(
            \App\Models\Paper::class,
            [
                'course_id' => $course1->id,
                'subcategory' => \App\Models\Paper::ADMIN_PRINT_READY_VERSION,
                'category' => 'main']
        );

        $response = $this->actingAs($admin)->get(route('paper.index'));

        $response->assertOk();
        $response->assertSee('Exam Paper List');
        $response->assertSee($course1->code);
        $response->assertSee($course2->code);
        $response->assertSee($printReadyPaper->created_at->format('d/m/Y'));
        $response->assertSee('No');

        $printReadyPaper->update(['print_ready_approved' => 'Y']);

        $response = $this->actingAs($admin)->get(route('paper.index'));

        $response->assertOk();
        $response->assertSee('Exam Paper List');
        $response->assertSee($course1->code);
        $response->assertSee($course2->code);
        $response->assertSee($printReadyPaper->created_at->format('d/m/Y'));
        $response->assertSee('Yes');

        $printReadyPaper->update(['print_ready_approved' => 'N']);
        $printReadyPaper->update(['print_ready_comment' => 'Big typo on page 3']);

        $response = $this->actingAs($admin)->get(route('paper.index'));

        $response->assertOk();
        $response->assertSee('Exam Paper List');
        $response->assertSee($course1->code);
        $response->assertSee($course2->code);
        $response->assertSee($printReadyPaper->created_at->format('d/m/Y'));
        $response->assertSee('Rejected');
        $response->assertSee('Big typo on page 3');
    }
}
