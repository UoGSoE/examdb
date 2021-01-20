<?php

namespace Tests\Feature\Admin;

use App\Course;
use App\Discipline;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_admin_can_see_the_list_of_all_courses()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $course1 = create(Course::class);
        $course2 = create(Course::class);

        $response = $this->actingAs($admin)->get(route('course.index'));

        $response->assertOk();
        $response->assertSee('Course List');
        $response->assertSee($course1->code);
        $response->assertSee($course2->code);
        $response->assertViewHas('courses');
        $response->assertViewHas('disciplines');
        $response->assertViewHas('disciplineFilter');
    }

    /** @test */
    public function an_admin_can_see_the_list_of_all_courses_for_a_specific_discipline()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $discipline1 = create(Discipline::class);
        $discipline2 = create(Discipline::class);
        $course1 = create(Course::class, ['discipline_id' => $discipline1->id]);
        $course2 = create(Course::class, ['discipline_id' => $discipline2->id]);

        $response = $this->actingAs($admin)->get(route('course.index', ['discipline' => $discipline2->id]));

        $response->assertOk();
        $response->assertSee('Course List');
        $response->assertDontSee($course1->code);
        $response->assertSee($course2->code);
    }

    /** @test */
    public function an_admin_can_export_the_list_of_courses_as_an_excel_sheet()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $course1 = create(Course::class);
        $course2 = create(Course::class);

        $response = $this->actingAs($admin)->get(route('admin.course.export'));

        $header = $response->headers->get('content-disposition');
        $this->assertEquals($header, "attachment; filename=examdb_courses_" . now()->format('d_m_Y_H_i') . ".xlsx");
    }

    /** @test */
    public function an_admin_can_disable_a_course()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $course1 = create(Course::class);
        $course2 = create(Course::class);

        $response = $this->actingAs($admin)->post(route('course.disable', $course2->id));

        $response->assertOk();
        $this->assertTrue($course2->fresh()->isDisabled());
        $this->assertFalse($course1->fresh()->isDisabled());
    }

    /** @test */
    public function an_admin_can_enable_a_course()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $course1 = create(Course::class);
        $course1->disable();
        $course2 = create(Course::class);

        $response = $this->actingAs($admin)->post(route('course.enable', $course1->id));

        $response->assertStatus(302);
        $response->assertRedirect(route('course.index', ['withtrashed' => 1]));
        $this->assertFalse($course2->fresh()->isDisabled());
        $this->assertFalse($course1->fresh()->isDisabled());
    }

    /** @test */
    public function an_admin_can_edit_a_course()
    {
        $this->withoutExceptionHandling();
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        $response = $this->actingAs($admin)->get(route('course.edit', $course->id));

        $response->assertOk();
        $response->assertViewHas('course', $course);

        $response = $this->actingAs($admin)->post(route('course.update', $course->id), [
            'code' => 'ENG9999',
            'title' => 'BLAH',
        ]);

        $response->assertRedirect(route('course.show', $course->id));
        tap($course->fresh(), function ($course) {
            $this->assertEquals('ENG9999', $course->code);
            $this->assertEquals('BLAH', $course->title);
        });
    }
}
