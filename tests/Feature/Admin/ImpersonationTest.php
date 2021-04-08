<?php

namespace Tests\Feature\Admin;

use App\User;
use Tests\TestCase;
use Tests\TenantTestCase;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ImpersonationTest extends TenantTestCase
{


    /** @test */
    public function an_admin_can_impersonate_other_users()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $user = create(User::class);

        $response = $this->actingAs($admin)->get(route('impersonate', $user));

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $this->assertEquals($user->id, auth()->user()->id);
        $response->assertSessionHas(['impersonated_by' => $admin->id]);

        $response = $this->get('/home');

        $response->assertSee('Stop impersonating');

        // and check we recorded this in the activity/audit log
        tap(Activity::first(), function ($log) use ($admin, $user) {
            $this->assertTrue($log->causer->is($admin));
            $this->assertEquals(
                "Started impersonating {$user->full_name}",
                $log->description
            );
        });
    }

    /** @test */
    public function regular_users_cant_impersonate_other_users()
    {
        $user1 = create(User::class);
        $user2 = create(User::class);

        $response = $this->actingAs($user1)->get(route('impersonate', $user2));

        $response->assertStatus(403);
        $this->assertEquals($user1->id, auth()->user()->id);
        $response->assertSessionMissing('impersonated_by');
    }

    /** @test */
    public function an_admin_can_stop_impersonating_other_users()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $user = create(User::class);

        $response = $this->actingAs($admin)->get(route('impersonate', $user));

        $this->assertEquals($user->id, auth()->user()->id);
        $response->assertSessionHas(['impersonated_by' => $admin->id]);

        $response = $this->actingAs($user)->get(route('impersonate.leave'));

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $this->assertEquals($admin->id, auth()->user()->id);
        $response->assertSessionMissing('impersonated_by');

        $activity = Activity::all();
        $logBeforeLoginEvent = $activity[count($activity) - 1];
        tap($logBeforeLoginEvent, function ($log) use ($admin, $user) {
            $this->assertTrue($log->causer->is($admin));
            $this->assertEquals(
                "Stopped impersonating {$user->full_name}",
                $log->description
            );
        });
    }
}
