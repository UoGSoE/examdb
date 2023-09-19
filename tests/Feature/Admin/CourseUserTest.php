<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CourseUserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        AcademicSession::createFirstSession();
    }

    /** @test */
    public function an_admin_can_update_the_staff_associated_with_a_course()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $course = create(Course::class);
        $setter1 = create(User::class);
        $setter2 = create(User::class);
        $moderator1 = create(User::class);
        $moderator2 = create(User::class);
        $external1 = create(User::class);
        $external2 = create(User::class);

        $response = $this->actingAs($admin)->postJson(route('course.users.update', $course), [
            'setters' => [
                $setter1->id,
                $setter2->id,
                $moderator2->id,
            ],
            'moderators' => [
                $moderator1->id,
                $moderator2->id,
                $setter1->id,
            ],
            'externals' => [
                $external1->id,
                $external2->id,
            ],
        ]);

        $response->assertOk();
        $course = $course->fresh();
        $this->assertCount(3, $course->setters);
        $this->assertTrue($course->setters->contains($setter1));
        $this->assertTrue($course->setters->contains($setter2));
        $this->assertTrue($course->setters->contains($moderator2));
        $this->assertCount(3, $course->moderators);
        $this->assertTrue($course->moderators->contains($moderator1));
        $this->assertTrue($course->moderators->contains($moderator2));
        $this->assertTrue($course->moderators->contains($setter1));
        $this->assertCount(2, $course->externals);
        $this->assertTrue($course->externals->contains($external1));
        $this->assertTrue($course->externals->contains($external2));
    }

    /** @test */
    public function someone_can_be_marked_as_a_setter_and_subsequently_be_added_as_a_moderator()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $course = create(Course::class);
        $setter1 = create(User::class);
        $setter2 = create(User::class);
        $moderator1 = create(User::class);
        $moderator2 = create(User::class);
        $setter1->markAsSetter($course);
        $setter2->markAsSetter($course);

        $response = $this->actingAs($admin)->postJson(route('course.users.update', $course), [
            'setters' => [
                $setter1->id,
                $setter2->id,
            ],
            'moderators' => [
                $moderator1->id,
                $moderator2->id,
                $setter1->id,
            ],
            'externals' => [
            ],
        ]);

        $response->assertOk();
        $course = $course->fresh();
        $this->assertCount(2, $course->setters);
        $this->assertTrue($course->setters->contains($setter1));
        $this->assertTrue($course->setters->contains($setter2));
        $this->assertCount(3, $course->moderators);
        $this->assertTrue($course->moderators->contains($moderator1));
        $this->assertTrue($course->moderators->contains($moderator2));
        $this->assertTrue($course->moderators->contains($setter1));
        $this->assertCount(0, $course->externals);
    }

    /** @test */
    public function someone_can_be_marked_as_a_moderator_and_subsequently_be_added_as_a_setter()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $course = create(Course::class);
        $setter1 = create(User::class);
        $setter2 = create(User::class);
        $moderator1 = create(User::class);
        $moderator2 = create(User::class);
        $setter1->markAsSetter($course);
        $setter2->markAsSetter($course);
        $moderator1->markAsModerator($course);
        $moderator2->markAsModerator($course);

        $response = $this->actingAs($admin)->postJson(route('course.users.update', $course), [
            'setters' => [
                $setter1->id,
                $setter2->id,
                $moderator1->id,
            ],
            'moderators' => [
                $moderator1->id,
                $moderator2->id,
                $setter1->id,
            ],
            'externals' => [
            ],
        ]);

        $response->assertOk();
        $course = $course->fresh();
        $this->assertCount(3, $course->setters);
        $this->assertTrue($course->setters->contains($setter1));
        $this->assertTrue($course->setters->contains($setter2));
        $this->assertTrue($course->setters->contains($moderator1));
        $this->assertCount(3, $course->moderators);
        $this->assertTrue($course->moderators->contains($moderator1));
        $this->assertTrue($course->moderators->contains($moderator2));
        $this->assertTrue($course->moderators->contains($setter1));
        $this->assertCount(0, $course->externals);
    }

    /** @test */
    public function an_admin_can_remove_staff_already_associated_with_a_course()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $course = create(Course::class);

        $setter1 = create(User::class);
        $setter1->markAsSetter($course);
        $setter2 = create(User::class);

        $moderator1 = create(User::class);
        $moderator2 = create(User::class);
        $moderator2->markAsModerator($course);

        $external1 = create(User::class);
        $external1->markAsExternal($course);
        $external2 = create(User::class);

        $response = $this->actingAs($admin)->postJson(route('course.users.update', $course), [
            'setters' => [
                $setter2->id,
            ],
            'moderators' => [
                $moderator1->id,
            ],
            'externals' => [
                $external2->id,
            ],
        ]);

        $response->assertOk();
        $course = $course->fresh();
        $this->assertCount(1, $course->setters);
        $this->assertTrue($course->setters->contains($setter2));
        $this->assertCount(1, $course->moderators);
        $this->assertTrue($course->moderators->contains($moderator1));
        $this->assertCount(1, $course->externals);
        $this->assertTrue($course->externals->contains($external2));
    }

    /** @test */
    public function admins_can_remove_all_staff_from_all_courses()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $course1 = create(Course::class);
        $course2 = create(Course::class);

        $setter1 = create(User::class);
        $setter1->markAsSetter($course1);
        $setter2 = create(User::class);
        $setter2->markAsSetter($course2);

        $moderator1 = create(User::class);
        $moderator2 = create(User::class);
        $moderator2->markAsModerator($course1);
        $moderator1->markAsModerator($course1);

        $external1 = create(User::class);
        $external1->markAsExternal($course2);

        $this->pretendPasswordConfirmed();

        $response = $this->actingAs($admin)->post(route('admin.courses.clear_staff'));

        $response->assertRedirect(route('course.index'));
        $response->assertSessionHasNoErrors();
        $this->assertEquals(0, $course1->fresh()->staff()->count());
        $this->assertEquals(0, $course2->fresh()->staff()->count());
    }

    /** @test */
    public function all_users_show_up_in_the_staff_list_admins_can_use_to_choose_who_are_setters_and_moderatorss()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $course = create(Course::class);
        $internal1 = create(User::class);
        $internal2 = create(User::class);
        $external = create(User::class, ['email' => 'jenny@example.com', 'is_external' => true]);

        $response = $this->actingAs($admin)->get(route('course.show', $course->id));

        $response->assertOk();
        $response->assertViewHas('staff');
        $response->data('staff')->pluck('value')->assertContains($internal1->id);
        $response->data('staff')->pluck('value')->assertContains($internal2->id);
        $response->data('staff')->pluck('value')->assertContains($external->id);
        $response->data('staff')->pluck('value')->assertContains($admin->id);
    }

    protected function pretendPasswordConfirmed()
    {
        session(['auth' => ['password_confirmed_at' => now()->timestamp]]);  // pretend we have confirmed our password
    }
}
