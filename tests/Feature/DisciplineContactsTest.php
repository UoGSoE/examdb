<?php

namespace Tests\Feature;

use App\Discipline;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DisciplineContactsTest extends TestCase
{
    use RefreshDatabase;

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
            ]
        ]);

        $response->assertOk();
        $this->assertEquals('someone@example.com', $disc1->fresh()->contact);
        $this->assertEquals('whatever@example.com', $disc2->fresh()->contact);
    }
}
