<?php

namespace Tests\Feature\Admin;

use App\Models\Course;
use App\Models\Discipline;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PaperTest extends TestCase
{
    use RefreshDatabase;

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

        $response = $this->actingAs($admin)->get(route('paper.index', ['discipline' => $discipline2->id]));

        $response->assertOk();
        $response->assertSee('Exam Paper List');
        $response->assertDontSee($course1->code);
        $response->assertSee($course2->code);
    }
}
