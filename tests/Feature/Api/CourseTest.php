<?php

namespace Tests\Feature\Api;

use App\Course;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function we_can_get_a_list_of_all_courses()
    {
        $this->withoutExceptionHandling();
        config(['exampapers.api_key' => 'secret']);
        $course1 = create(Course::class, ['code' => 'ENG1000']);
        $course2 = create(Course::class, ['code' => 'ENG2000']);

        $response = $this->getJson(route('api.course.index'), ['x-api-key' => 'secret']);

        $response->assertOk();
        $response->assertJson([
            'data' => [
                ['id' => $course1->id, 'code' => $course1->code],
                ['id' => $course2->id, 'code' => $course2->code],
            ]
        ]);
    }

    /** @test */
    public function we_can_get_the_details_for_one_course()
    {
        $this->withoutExceptionHandling();
        config(['exampapers.api_key' => 'secret']);
        $course1 = create(Course::class, ['code' => 'ENG1000']);

        $response = $this->getJson(route('api.course.show', $course1->code), ['x-api-key' => 'secret']);

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'id' => $course1->id,
                'code' => $course1->code,
            ]
        ]);
    }
}
