<?php

namespace Tests\Feature\Admin;

use App\User;
use App\Course;
use App\Discipline;
use Tests\TestCase;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_admin_can_see_the_list_of_all_courses()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $course1 = Course::factory()->create();
        $course2 = Course::factory()->notExamined()->create();

        $response = $this->actingAs($admin)->get(route('course.index'));

        $response->assertOk();
        $response->assertSee('Course List');
        $response->assertSee($course1->code);
        $response->assertSee($course2->code);
        $response->assertSeeLivewire('course-index');
    }

    /** @test */
    public function an_admin_can_filter_the_list_of_courses_in_various_ways()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $discipline1 = create(Discipline::class);
        $discipline2 = create(Discipline::class);
        $course1 = create(Course::class, ['discipline_id' => $discipline1->id]);
        $course2 = create(Course::class, ['discipline_id' => $discipline2->id]);
        $course3 = Course::factory()->notExamined()->create(['discipline_id' => $discipline1->id]);
        $course4 = Course::factory()->create(['discipline_id' => $discipline2->id]);
        $course4->delete();

        Livewire::actingAs($admin)->test('course-index')
            ->assertSee($course1->code)
            ->assertSee($course2->code)
            ->assertSee($course3->code)
            ->assertDontSee($course4->code)
            ->set('includeTrashed', true)
            ->assertSee($course4->code)
            ->set('excludeNotExamined', true)
            ->assertDontSee($course3->code)
            ->set('excludeNotExamined', false)
            ->assertSee($course3->code)
            ->set('disciplineFilter', $discipline1->id)
            ->assertSee($course1->code)
            ->assertDontSee($course2->code)
            ->assertSee($course3->code)
            ->assertDontSee($course4->code);
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
        $discipline1 = Discipline::factory()->create();
        $discipline2 = Discipline::factory()->create();
        $course = Course::factory()->create(['discipline_id' => $discipline1->id]);

        $response = $this->actingAs($admin)->get(route('course.edit', $course->id));

        $response->assertOk();
        $response->assertViewHas('course', $course);

        $response = $this->actingAs($admin)->post(route('course.update', $course->id), [
            'code' => 'ENG9999',
            'title' => 'BLAH',
            'discipline_id' => $discipline2->id,
            'is_examined' => 0,
        ]);

        $response->assertRedirect(route('course.show', $course->id));
        tap($course->fresh(), function ($course) use ($discipline2) {
            $this->assertEquals('ENG9999', $course->code);
            $this->assertEquals('BLAH', $course->title);
            $this->assertTrue($course->discipline->is($discipline2));
            $this->assertFalse($course->isExamined());
        });
    }

    /** @test */
    public function we_can_call_an_artisan_command_to_make_a_deep_clone_of_an_existing_course_with_a_new_course_code()
    {
        $discipline1 = Discipline::factory()->create();
        $discipline2 = Discipline::factory()->create();
        $course = Course::factory()->create(['code' => 'ENG1234', 'semester' => 2, 'discipline_id' => $discipline1->id]);
        $setter1 = User::factory()->create();
        $setter2 = User::factory()->create();
        $moderator = User::factory()->create();
        $external = User::factory()->create();
        $setter1->markAsSetter($course);
        $setter2->markAsSetter($course);
        $moderator->markAsModerator($course);
        $external->markAsExternal($course);

        $this->artisan('examdb:duplicate-course', ['code' => 'ENG1234', 'newcode' => 'ENG5678_2']);

        tap(Course::findByCode('ENG5678_2'), function ($course) use ($discipline1, $setter1, $setter2, $moderator, $external) {
            $this->assertTrue($course->semester == 2);
            $this->assertTrue($course->discipline->is($discipline1));
            $this->assertCount(2, $course->setters);
            $this->assertTrue($course->setters->contains($setter1));
            $this->assertTrue($course->setters->contains($setter2));
            $this->assertTrue($course->moderators->contains($moderator));
            $this->assertTrue($course->externals->contains($external));
        });
    }
}
