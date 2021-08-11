<?php

namespace Tests\Feature\Api;

use App\User;
use App\Course;
use Tests\TestCase;
use App\AcademicSession;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CourseStaffTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        AcademicSession::createFirstSession();
    }

    /** @test */
    public function we_can_get_the_staff_associated_with_a_course()
    {
        config(['exampapers.api_key' => 'secret']);
        $course = create(Course::class);
        $setter = create(User::class);
        $moderator = create(User::class);
        $external = create(User::class);
        $setter->markAsSetter($course);
        $moderator->markAsModerator($course);
        $external->markAsExternal($course);

        $response = $this->getJson(route('api.course.staff', $course->code), ['x-api-key' => 'secret']);

        $response->assertOk();
        $response->assertJson([
            'course' => [
                'code' => $course->code,
                'setters' => [
                    [
                        'id' => $setter->id,
                        'surname' => $setter->surname,
                        'email' => $setter->email,
                    ],
                ],
                'moderators' => [
                    [
                        'id' => $moderator->id,
                        'surname' => $moderator->surname,
                        'email' => $moderator->email,
                    ],
                ],
                'externals' => [
                    [
                        'id' => $external->id,
                        'surname' => $external->surname,
                        'email' => $external->email,
                    ],
                ],
            ],
        ]);
    }

    /** @test */
    public function trying_to_get_info_for_an_invalid_course_returns_a_404()
    {
        config(['exampapers.api_key' => 'secret']);

        $response = $this->getJson(route('api.course.staff', 'BLAH3456'), ['x-api-key' => 'secret']);

        $response->assertStatus(404);
    }

    /** @test */
    public function an_invalid_api_key_is_denied_access()
    {
        config(['exampapers.api_key' => 'secret']);
        $course = create(Course::class);

        $response = $this->getJson(route('api.course.staff', $course->code), ['x-api-key' => 'wrongsecret']);

        $response->assertStatus(401);
    }
}
