<?php

namespace Tests\Feature\Admin;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Course;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admins_can_see_a_list_of_all_users()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $internal1 = create(User::class);
        $internal2 = create(User::class);
        $external1 = factory(User::class)->states('external')->create();
        $external2 = factory(User::class)->states('external')->create();

        $response = $this->actingAs($admin)->get(route('user.index'));

        $response->assertOk();
        $response->assertSee("Current Users");
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
    public function admins_can_see_the_details_for_a_given_user()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $internalUser = create(User::class);
        $externalUser = factory(User::class)->states('external')->create();
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
    public function admins_can_create_a_new_local_user()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);

        $response = $this->actingAs($admin)->postJson(route('user.store'), [
            'username' => 'test1x',
            'email' => 'test@example.com',
            'surname' => 'McTest',
            'forenames' => 'Test'
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'user' => [
                'username' => 'test1x',
                'email' => 'test@example.com',
                'surname' => 'McTest',
                'forenames' => 'Test',
                'is_external' => false,
            ]
        ]);
        $this->assertDatabaseHas('users', [
            'username' => 'test1x',
            'email' => 'test@example.com',
            'surname' => 'McTest',
            'forenames' => 'Test',
            'is_external' => false,
        ]);
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
            'forenames' => 'Test'
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'user' => [
                'username' => 'test@example.com',
                'email' => 'test@example.com',
                'surname' => 'McTest',
                'forenames' => 'Test',
                'is_external' => true,
            ]
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
    public function regular_users_cant_create_users()
    {
        $user = create(User::class);

        $response = $this->actingAs($user)->postJson(route('user.store'), [
            'username' => 'test@example.com',
            'email' => 'test@example.com',
            'surname' => 'McTest',
            'forenames' => 'Test'
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('users', [
            'username' => 'test@example.com'
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
            'forenames' => 'Test'
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'user' => [
                'username' => 'test@example.com',
                'email' => 'test@example.com',
                'surname' => 'McTest',
                'forenames' => 'Test',
                'is_external' => true,
            ]
        ]);
        $this->assertDatabaseHas('users', [
            'username' => 'test@example.com',
            'email' => 'test@example.com',
            'surname' => 'McTest',
            'forenames' => 'Test',
            'is_external' => true,
        ]);
    }
}
