<?php

namespace Tests\Feature\Api;

use App\Course;
use App\Paper;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TenantTestCase;
use Tests\TestCase;

class CoursePapersTest extends TenantTestCase
{
    use RefreshDatabase;

    /** @test */
    public function we_can_get_a_list_of_papers_for_a_course()
    {
        $this->withoutExceptionHandling();
        config(['exampapers.api_key' => 'secret']);
        $course1 = create(Course::class, ['code' => 'ENG1000']);
        $paper1 = create(Paper::class, ['course_id' => $course1->id]);
        $paper2 = create(Paper::class, ['course_id' => $course1->id]);
        $paper3 = create(Paper::class);

        $response = $this->getJson(route('api.course.papers', $course1->code), ['x-api-key' => 'secret']);

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
        $response->assertJson([
            'data' => [
                ['id' => $paper1->id, 'category' => $paper1->category],
                ['id' => $paper2->id, 'subcategory' => $paper2->subcategory],
            ],
        ]);
    }
}
