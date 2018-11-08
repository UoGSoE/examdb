<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admins_can_see_the_activity_log_page()
    {
        $admin = create(User::class, ['is_admin' => true]);
        activity()->log('Ate some crisps');
        activity()->log('Had some juice');

        $response = $this->actingAs($admin)->get(route('activity.index'));

        $response->assertOk();
        $response->assertSee('Activity Log');
        $response->assertSee('Ate some crisps');
        $response->assertSee('Had some juice');
    }

    /** @test */
    public function regular_users_cant_see_the_activity_log_page()
    {
        $admin = create(User::class, ['is_admin' => false]);

        $response = $this->actingAs($admin)->get(route('activity.index'));

        $response->assertStatus(403);
    }
}
