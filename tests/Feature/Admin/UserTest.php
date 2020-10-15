<?php

namespace Tests\Feature\Admin;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Ohffs\Ldap\FakeLdapConnection;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admins_can_see_a_list_of_all_users()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $internal1 = create(User::class);
        $internal2 = create(User::class);
        $external1 = User::factory()->external()->create();
        $external2 = User::factory()->external()->create();

        $response = $this->actingAs($admin)->get(route('user.index'));

        $response->assertOk();
        $response->assertSee('Current Users');
        $response->assertSee($internal1->surname);
        $response->assertSee($internal2->surname);
        $response->assertSee($external1->surname);
        $response->assertSee($external2->surname);
    }

    /** @test */
    public function regular_users_cant_see_a_list_of_all_users()
    {
        $user = create(User::class, ['is_admin' => false]);

        $response = $this->actingAs($user)->get(route('user.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function admins_can_see_a_list_of_all_users_including_soft_deleted_ones()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $internal1 = create(User::class);
        $internal2 = create(User::class);
        $external1 = User::factory()->external()->create();
        $external2 = User::factory()->external()->create();
        $deletedUser = create(User::class);
        $deletedUser->delete();

        $response = $this->actingAs($admin)->get(route('user.index', ['withtrashed' => true]));

        $response->assertOk();
        $response->assertSee('Current Users');
        $response->assertSee($internal1->surname);
        $response->assertSee($internal2->surname);
        $response->assertSee($external1->surname);
        $response->assertSee($external2->surname);
        $response->assertSee($deletedUser->surname);
    }

    /** @test */
    public function admins_can_see_the_details_for_a_given_user()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $internalUser = create(User::class);
        $externalUser = User::factory()->external()->create();
        $course1 = create(Course::class);
        $course2 = create(Course::class);
        $course3 = create(Course::class);
        $internalUser->markAsSetter($course1);
        $internalUser->markAsModerator($course2);
        $externalUser->markAsExternal($course3);

        $response = $this->actingAs($admin)->get(route('user.show', $internalUser));

        $response->assertOk();
        $response->assertSee($internalUser->full_name);
        $response->assertSee($course1->code);
        $response->assertSee($course2->code);
        $response->assertDontSee($course3->code);

        $response = $this->actingAs($admin)->get(route('user.show', $externalUser));

        $response->assertOk();
        $response->assertSee($externalUser->full_name);
        $response->assertDontSee($course1->code);
        $response->assertDontSee($course2->code);
        $response->assertSee($course3->code);
    }

    /** @test */
    public function regular_users_cant_see_the_details_for_users()
    {
        $user = create(User::class);

        $response = $this->actingAs($user)->get(route('user.show', $user));

        $response->assertStatus(403);
    }

    /** @test */
    public function admins_can_search_for_a_guid()
    {
        $this->withoutExceptionHandling();
        $this->app->bind('Ohffs\Ldap\LdapConnectionInterface', function ($app) {
            return new FakeLdapConnection('a', 'b');
        });
        $admin = create(User::class, ['is_admin' => true]);

        $response = $this->actingAs($admin)->getJson(route('user.search', ['guid' => 'validuser']));

        $response->assertOk();
        $response->assertJson([
            'user' => [
                'username' => 'validuser',
            ],
        ]);
    }

    /** @test */
    public function invalid_guid_searches_return_a_404()
    {
        $this->app->bind('Ohffs\Ldap\LdapConnectionInterface', function ($app) {
            return new FakeLdapConnection('a', 'b');
        });
        $admin = create(User::class, ['is_admin' => true]);

        $response = $this->actingAs($admin)->getJson(route('user.search', ['guid' => 'invaliduser']));

        $response->assertStatus(404);
    }

    /** @test */
    public function admins_can_create_a_new_local_user()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);

        $response = $this->actingAs($admin)->postJson(route('user.store'), [
            'username' => 'test1x',
            'email' => 'test@example.com',
            'surname' => 'McTest',
            'forenames' => 'Test',
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'user' => [
                'username' => 'test1x',
                'email' => 'test@example.com',
                'surname' => 'McTest',
                'forenames' => 'Test',
                'is_external' => false,
            ],
        ]);
        $this->assertDatabaseHas('users', [
            'username' => 'test1x',
            'email' => 'test@example.com',
            'surname' => 'McTest',
            'forenames' => 'Test',
            'is_external' => false,
        ]);
        // and check we recorded this in the activity/audit log
        tap(Activity::all()->last(), function ($log) use ($admin) {
            $this->assertTrue($log->causer->is($admin));
            $this->assertEquals(
                "Created new local user 'test1x'",
                $log->description
            );
        });
    }

    /** @test */
    public function admins_can_create_a_new_external_user()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);

        $response = $this->actingAs($admin)->postJson(route('user.store'), [
            'username' => 'test@example.com',
            'email' => 'test@example.com',
            'surname' => 'McTest',
            'forenames' => 'Test',
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'user' => [
                'username' => 'test@example.com',
                'email' => 'test@example.com',
                'surname' => 'McTest',
                'forenames' => 'Test',
                'is_external' => true,
            ],
        ]);
        $this->assertDatabaseHas('users', [
            'username' => 'test@example.com',
            'email' => 'test@example.com',
            'surname' => 'McTest',
            'forenames' => 'Test',
            'is_external' => true,
        ]);
        // and check we recorded this in the activity/audit log
        tap(Activity::all()->last(), function ($log) use ($admin) {
            $this->assertTrue($log->causer->is($admin));
            $this->assertEquals(
                "Created new external 'test@example.com'",
                $log->description
            );
        });
    }

    /** @test */
    public function regular_users_cant_create_users()
    {
        $user = create(User::class);

        $response = $this->actingAs($user)->postJson(route('user.store'), [
            'username' => 'test@example.com',
            'email' => 'test@example.com',
            'surname' => 'McTest',
            'forenames' => 'Test',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('users', [
            'username' => 'test@example.com',
        ]);
    }

    /** @test */
    public function emails_and_usernames_are_converted_to_lowercase_when_creating_new_users()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);

        $response = $this->actingAs($admin)->postJson(route('user.store'), [
            'username' => 'TEST@EXAMPLE.COM',
            'email' => 'TEST@EXAMPLE.COM',
            'surname' => 'McTest',
            'forenames' => 'Test',
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'user' => [
                'username' => 'test@example.com',
                'email' => 'test@example.com',
                'surname' => 'McTest',
                'forenames' => 'Test',
                'is_external' => true,
            ],
        ]);
        $this->assertDatabaseHas('users', [
            'username' => 'test@example.com',
            'email' => 'test@example.com',
            'surname' => 'McTest',
            'forenames' => 'Test',
            'is_external' => true,
        ]);
    }

    /** @test */
    public function admins_can_soft_delete_users()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $user = create(User::class, ['is_admin' => false]);

        $response = $this->actingAs($admin)->deleteJson(route('admin.user.delete', $user->id));

        $response->assertOk();
        $response->assertJsonMissingValidationErrors();
        $this->assertTrue($user->fresh()->trashed());
    }

    /** @test */
    public function admins_can_un_soft_delete_users()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $user = create(User::class, ['is_admin' => false]);
        $user->delete();

        $response = $this->actingAs($admin)->post(route('admin.user.undelete', $user->id));

        $response->assertOk();
        $this->assertFalse($user->fresh()->trashed());
    }

    /** @test */
    public function admins_can_toggle_admin_status_of_other_users()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $user = create(User::class, ['is_admin' => false]);

        $response = $this->actingAs($admin)->postJson(route('admin.toggle', $user->id));

        $response->assertOk();
        $response->assertJson([
            'user' => [
                'id' => $user->id,
                'is_admin' => true,
            ],
        ]);
        $this->assertTrue($user->fresh()->isAdmin());
        // and check we recorded this in the activity/audit log
        tap(Activity::all()->last(), function ($log) use ($admin, $user) {
            $this->assertTrue($log->causer->is($admin));
            $this->assertEquals(
                "Toggled admin status for {$user->full_name}",
                $log->description
            );
        });

        $response = $this->actingAs($admin)->postJson(route('admin.toggle', $user->id));

        $response->assertOk();
        $response->assertJson([
            'user' => [
                'id' => $user->id,
                'is_admin' => false,
            ],
        ]);
        $this->assertFalse($user->fresh()->isAdmin());

        // and check we recorded this in the activity/audit log
        tap(Activity::all()->last(), function ($log) use ($admin, $user) {
            $this->assertTrue($log->causer->is($admin));
            $this->assertEquals(
                "Toggled admin status for {$user->full_name}",
                $log->description
            );
        });
    }

    /** @test */
    public function regular_users_cant_toggle_admin_status_of_users()
    {
        $user1 = create(User::class, ['is_admin' => false]);
        $user2 = create(User::class, ['is_admin' => false]);

        $response = $this->actingAs($user1)->postJson(route('admin.toggle', $user2->id));

        $response->assertStatus(403);
        $this->assertFalse($user2->fresh()->isAdmin());
    }

    /** @test */
    public function admins_cant_toggle_their_own_admin_status()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);

        $response = $this->actingAs($admin)->postJson(route('admin.toggle', $admin->id));

        $response->assertStatus(409);
        $this->assertTrue($admin->fresh()->isAdmin());
    }

    /** @test */
    public function there_is_an_artisan_command_to_make_a_user_an_admin()
    {
        $user = create(User::class, ['username' => 'jenny']);

        $this->assertFalse($user->isAdmin());

        Artisan::call('exampapers:makeadmin jenny');

        $this->assertTrue($user->fresh()->isAdmin());
    }
}
