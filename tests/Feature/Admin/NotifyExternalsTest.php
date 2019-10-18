<?php

namespace Tests\Feature\Admin;

use App\User;
use Tests\TestCase;
use App\Jobs\NotifyExternals;
use Illuminate\Support\Facades\Bus;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NotifyExternalsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admins_can_trigger_a_job_to_notify_externals_to_look_at_the_system()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);

        Bus::fake();

        $response = $this->actingAs($admin)->post(route('admin.notify.externals', [
            'area' => 'glasgow',
        ]));

        $response->assertStatus(200);
        $response->assertJsonMissingValidationErrors();

        Bus::assertDispatched(NotifyExternals::class, function ($job) {
            return $job->area === 'glasgow';
        });
    }

    /** @test */
    public function non_admins_cant_trigger_a_job_to_notify_externals()
    {
        $user = create(User::class);

        Bus::fake();

        $response = $this->actingAs($user)->post(route('admin.notify.externals', [
            'area' => 'glasgow',
        ]));

        $response->assertStatus(403);

        Bus::assertNotDispatched(NotifyExternals::class);
    }
}
