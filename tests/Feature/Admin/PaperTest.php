<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\Course;

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
    }
}
