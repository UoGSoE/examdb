<?php

namespace Tests\Feature\Admin;

use App\User;
use App\Course;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
}
