<?php

namespace Tests\Feature\Admin;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OptionsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function regular_users_cant_see_the_admin_options_page()
    {
        $user = create(User::class);

        $response = $this->actingAs($user)->get(route('admin.options.edit'));

        $response->assertStatus(403);
    }

    /** @test */
    public function admins_can_see_the_admin_options_page()
    {
        $admin = create(User::class, ['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.options.edit'));

        $response->assertStatus(200);
        $response->assertViewHas('options');
    }

    /** @test */
    public function admins_can_set_the_system_options()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);

        $this->assertNull(option('main_deadline'));
        $response = $this->actingAs($admin)->postJson(route('admin.options.update'), [
            'main_deadline' => '30/01/2019'
        ]);
        $response->assertStatus(200);
        $this->assertEquals('2019-01-30', option('main_deadline'));

        $this->assertNull(option('teaching_office_contact'));
        $response = $this->actingAs($admin)->postJson(route('admin.options.update'), [
            'teaching_office_contact' => 'jenny@example.com'
        ]);
        $response->assertStatus(200);
        $this->assertEquals('jenny@example.com', option('teaching_office_contact'));
    }

    /** @test */
    public function options_have_to_be_in_valid_formats()
    {
        $admin = create(User::class, ['is_admin' => true]);

        $this->assertNull(option('main_deadline'));
        $response = $this->actingAs($admin)->postJson(route('admin.options.update'), [
            'main_deadline' => 'MUFFINS FOR EVERYONE!'
        ]);
        $response->assertStatus(422);
        $this->assertNull(option('main_deadline'));

        $this->assertNull(option('teaching_office_contact'));
        $response = $this->actingAs($admin)->postJson(route('admin.options.update'), [
            'teaching_office_contact' => 'jenny at example.com'
        ]);
        $response->assertStatus(422);
        $this->assertNull(option('teaching_office_contact'));
    }
}
