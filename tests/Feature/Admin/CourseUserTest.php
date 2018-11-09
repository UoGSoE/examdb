<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\Course;

class CourseUserTest extends TestCase
{
    use RefreshDatabase;

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
            ],
            'moderators' => [
                $moderator1->id,
                $moderator2->id,
            ],
            'externals' => [
                $external1->id,
                $external2->id,
            ],
        ]);

        $response->assertOk();
        $course = $course->fresh();
        $this->assertCount(2, $course->setters);
        $this->assertTrue($course->setters->contains($setter1));
        $this->assertTrue($course->setters->contains($setter2));
        $this->assertCount(2, $course->moderators);
        $this->assertTrue($course->moderators->contains($moderator1));
        $this->assertTrue($course->moderators->contains($moderator2));
        $this->assertCount(2, $course->externals);
        $this->assertTrue($course->externals->contains($external1));
        $this->assertTrue($course->externals->contains($external2));
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
}
