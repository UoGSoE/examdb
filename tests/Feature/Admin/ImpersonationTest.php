<?php

namespace Tests\Feature\Admin;

use App\User;
use Tests\TestCase;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ImpersonationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_admin_can_impersonate_other_users()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $user = create(User::class);

        $response = $this->actingAs($admin)->post(route('impersonate.start', $user));

        $response->assertStatus(302);
        $response->assertRedirect(route('home'));
        $this->assertEquals($user->id, auth()->user()->id);
        $response->assertSessionHas(['original_id' => $admin->id]);

        $response = $this->get(route('home'));

        $response->assertSee("Stop impersonating");

        // and check we recorded this in the activity/audit log
        $activity = Activity::all();
        $logBeforeLoginEvent = $activity[count($activity) - 2];
        tap($logBeforeLoginEvent, function ($log) use ($admin, $user) {
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

        $response = $this->actingAs($user1)->post(route('impersonate.start', $user2));

        $response->assertStatus(403);
        $this->assertEquals($user1->id, auth()->user()->id);
        $response->assertSessionMissing('original_id');
    }

    /** @test */
    public function an_admin_can_stop_impersonating_other_users()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $user = create(User::class);

        $response = $this->actingAs($admin)->post(route('impersonate.start', $user));

        $this->assertEquals($user->id, auth()->user()->id);
        $response->assertSessionHas(['original_id' => $admin->id]);

        $response = $this->actingAs($user)->post(route('impersonate.stop'));

        $response->assertStatus(302);
        $response->assertRedirect(route('home'));
        $this->assertEquals($admin->id, auth()->user()->id);
        $response->assertSessionMissing('original_id');

        $activity = Activity::all();
        $logBeforeLoginEvent = $activity[count($activity) - 2];
        tap($logBeforeLoginEvent, function ($log) use ($admin, $user) {
            $this->assertTrue($log->causer->is($admin));
            $this->assertEquals(
                "Stopped impersonating {$user->full_name}",
                $log->description
            );
        });
    }
}
