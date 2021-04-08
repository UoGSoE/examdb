<?php

namespace Tests\Feature;

use App\Course;
use App\Discipline;
use App\Paper;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TenantTestCase;
use Tests\TestCase;

class DisciplineContactsTest extends TenantTestCase
{


    /** @test */
    public function admins_can_update_the_contacts_for_each_discipline()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $disc1 = create(Discipline::class);
        $disc2 = create(Discipline::class);

        $response = $this->actingAs($admin)->postJson(route('discipline.contacts.update'), [
            'disciplines' => [
                ['id' => $disc1->id, 'contact' => 'someone@example.com'],
                ['id' => $disc2->id, 'contact' => 'whatever@example.com'],
            ],
        ]);

        $response->assertOk();
        $this->assertEquals('someone@example.com', $disc1->fresh()->contact);
        $this->assertEquals('whatever@example.com', $disc2->fresh()->contact);
    }

    /** @test */
    public function we_can_get_the_discipline_contact_for_a_paper()
    {
        $disc1 = create(Discipline::class, ['contact' => 'jenny@example.com']);
        $course1 = create(Course::class, ['discipline_id' => $disc1->id]);
        $paper1 = create(Paper::class, ['course_id' => $course1->id]);

        $this->assertEquals('jenny@example.com', $paper1->getDisciplineContact());
    }

    /** @test */
    public function if_there_is_no_contact_for_the_discipline_we_fall_back_to_a_default()
    {
        config(['exampapers.fallback_email' => 'jimmy@example.com']);
        $disc1 = create(Discipline::class, ['contact' => null]);
        $course1 = create(Course::class, ['discipline_id' => $disc1->id]);
        $paper1 = create(Paper::class, ['course_id' => $course1->id]);

        $this->assertEquals('jimmy@example.com', $paper1->getDisciplineContact());
    }
}
