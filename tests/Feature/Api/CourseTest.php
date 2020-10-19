<?php

namespace Tests\Feature\Api;

use App\Course;
use App\Paper;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

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
            ],
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
            ],
        ]);
    }

    /** @test */
    public function we_can_get_a_list_of_applicable_drop_down_options_for_setters_add_paper_button()
    {
        $this->withoutExceptionHandling();
        config(['exampapers.api_key' => 'secret']);
        $course = create(Course::class);
        $setter = User::factory()->create();
        $setter->markAsSetter($course);

        $response = $this->actingAs($setter)->json(
            'GET',
            route('api.course.paper_options', $course->code),
            [
                'category' => 'main',
                'subcategory' => 'main',
            ],
            ['x-api-key' => 'secret']
        );

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'Pre-Internally Moderated Paper (Main)',
            ],
        ]);

        $course->papers()->save(make(Paper::class, ['category' => 'main', 'subcategory' => 'Moderator Comments']));

        $response = $this->actingAs($setter)->json(
            'GET',
            route('api.course.paper_options', $course->code),
            [
                'category' => 'main',
                'subcategory' => 'main',
            ],
            ['x-api-key' => 'secret']
        );

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'Pre-Internally Moderated Paper (Main)',
                'Post-Internally Moderated Paper (Main)',
            ],
        ]);

        $course->papers()->save(make(Paper::class, ['category' => 'main', 'subcategory' => 'Post-Internally Moderated Paper']));

        $response = $this->actingAs($setter)->json(
            'GET',
            route('api.course.paper_options', $course->code),
            [
                'category' => 'main',
                'subcategory' => 'main',
            ],
            ['x-api-key' => 'secret']
        );

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'Post-Internally Moderated Paper (Main)',
                'Response To External Examiner (Main)',
                'Paper For Registry (Main)',
            ],
        ]);
    }

    /** @test */
    public function we_can_get_a_list_of_applicable_drop_down_options_for_moderators_add_paper_button()
    {
        $this->withoutExceptionHandling();
        config(['exampapers.api_key' => 'secret']);
        $course = create(Course::class);
        $setter = User::factory()->create();
        $setter->markAsModerator($course);

        $response = $this->actingAs($setter)->json(
            'GET',
            route('api.course.paper_options', $course->code),
            [
                'category' => 'main',
                'subcategory' => 'main',
            ],
            ['x-api-key' => 'secret']
        );

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'Moderator Comments (Main)',
            ],
        ]);

        // all the moderator can do is add a comments paper - so it shouldn't matter if there is any other papers
        $course->papers()->save(make(Paper::class, ['category' => 'main', 'subcategory' => 'Moderator Comments']));

        $response = $this->actingAs($setter)->json(
            'GET',
            route('api.course.paper_options', $course->code),
            [
                'category' => 'main',
                'subcategory' => 'main',
            ],
            ['x-api-key' => 'secret']
        );

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'Moderator Comments (Main)',
            ],
        ]);
    }

    /** @test */
    public function we_can_get_a_list_of_applicable_drop_down_options_for_externals_add_paper_button()
    {
        $this->withoutExceptionHandling();
        config(['exampapers.api_key' => 'secret']);
        $course = create(Course::class);
        $setter = User::factory()->create();
        $setter->markAsExternal($course);

        $response = $this->actingAs($setter)->json(
            'GET',
            route('api.course.paper_options', $course->code),
            [
                'category' => 'main',
                'subcategory' => 'main',
            ],
            ['x-api-key' => 'secret']
        );

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'External Examiner Comments (Main)',
            ],
        ]);

        // all the external can do is add a comments paper - so it shouldn't matter if there is any other papers
        $course->papers()->save(make(Paper::class, ['category' => 'main', 'subcategory' => 'Moderator Comments']));

        $response = $this->actingAs($setter)->json(
            'GET',
            route('api.course.paper_options', $course->code),
            [
                'category' => 'main',
                'subcategory' => 'main',
            ],
            ['x-api-key' => 'secret']
        );

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'External Examiner Comments (Main)',
            ],
        ]);
    }

    /** @test */
    public function if_someone_is_a_setter_and_moderator_for_the_same_course_they_see_the_right_add_paper_options()
    {
        $this->withoutExceptionHandling();
        config(['exampapers.api_key' => 'secret']);
        $course = create(Course::class);
        $setter = User::factory()->create();
        $setter->markAsSetter($course);
        $setter->markAsModerator($course);

        $response = $this->actingAs($setter)->json(
            'GET',
            route('api.course.paper_options', $course->code),
            [
                'category' => 'main',
                'subcategory' => 'main',
            ],
            ['x-api-key' => 'secret']
        );

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'Pre-Internally Moderated Paper (Main)',
                'Moderator Comments (Main)',
            ],
        ]);

        $course->papers()->save(make(Paper::class, ['category' => 'main', 'subcategory' => 'Pre-Internally Moderated Paper']));
        $course->papers()->save(make(Paper::class, ['category' => 'main', 'subcategory' => 'Moderator Comments']));

        $response = $this->actingAs($setter)->json(
            'GET',
            route('api.course.paper_options', $course->code),
            [
                'category' => 'main',
                'subcategory' => 'main',
            ],
            ['x-api-key' => 'secret']
        );

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'Pre-Internally Moderated Paper (Main)',
                'Post-Internally Moderated Paper (Main)',
                'Moderator Comments (Main)',
            ],
        ]);
    }
}
