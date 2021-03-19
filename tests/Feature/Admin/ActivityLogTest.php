<?php

namespace Tests\Feature\Admin;

use App\User;
use Tests\TestCase;
use Tests\TenantTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ActivityLogTest extends TenantTestCase
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
