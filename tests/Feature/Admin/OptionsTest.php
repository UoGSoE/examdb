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

        // external deadlines
        $this->assertNull(option('external_deadline_glasgow'));
        $response = $this->actingAs($admin)->postJson(route('admin.options.update'), [
            'external_deadline_glasgow' => '30/01/2019'
        ]);
        $response->assertStatus(200);
        $this->assertEquals('2019-01-30', option('external_deadline_glasgow'));

        $this->assertNull(option('external_deadline_uestc'));
        $response = $this->actingAs($admin)->postJson(route('admin.options.update'), [
            'external_deadline_uestc' => '28/02/2019'
        ]);
        $response->assertStatus(200);
        $this->assertEquals('2019-02-28', option('external_deadline_uestc'));

        // internal deadlines
        $this->assertNull(option('internal_deadline_glasgow'));
        $response = $this->actingAs($admin)->postJson(route('admin.options.update'), [
            'internal_deadline_glasgow' => '30/01/2019'
        ]);
        $response->assertStatus(200);
        $this->assertEquals('2019-01-30', option('internal_deadline_glasgow'));

        $this->assertNull(option('internal_deadline_uestc'));
        $response = $this->actingAs($admin)->postJson(route('admin.options.update'), [
            'internal_deadline_uestc' => '28/02/2019'
        ]);
        $response->assertStatus(200);
        $this->assertEquals('2019-02-28', option('internal_deadline_uestc'));

        // contacts
        $this->assertNull(option('teaching_office_contact_glasgow'));
        $response = $this->actingAs($admin)->postJson(route('admin.options.update'), [
            'teaching_office_contact_glasgow' => 'jenny@example.com'
        ]);
        $response->assertStatus(200);
        $this->assertEquals('jenny@example.com', option('teaching_office_contact_glasgow'));

        $this->assertNull(option('teaching_office_contact_uestc'));
        $response = $this->actingAs($admin)->postJson(route('admin.options.update'), [
            'teaching_office_contact_uestc' => 'jenny@example.com'
        ]);
        $response->assertStatus(200);
        $this->assertEquals('jenny@example.com', option('teaching_office_contact_uestc'));
    }

    /** @test */
    public function options_have_to_be_in_valid_formats()
    {
        $admin = create(User::class, ['is_admin' => true]);

        $this->assertNull(option('internal_deadline_glasgow'));
        $response = $this->actingAs($admin)->postJson(route('admin.options.update'), [
            'internal_deadline_glasgow' => 'MUFFINS FOR EVERYONE!'
        ]);
        $response->assertStatus(422);
        $this->assertNull(option('internal_deadline_glasgow'));

        $this->assertNull(option('external_deadline_glasgow'));
        $response = $this->actingAs($admin)->postJson(route('admin.options.update'), [
            'external_deadline_glasgow' => 'MUFFINS FOR EVERYONE!'
        ]);
        $response->assertStatus(422);
        $this->assertNull(option('external_deadline_glasgow'));

        $this->assertNull(option('teaching_office_contact_uestc'));
        $response = $this->actingAs($admin)->postJson(route('admin.options.update'), [
            'teaching_office_contact_uestc' => 'jenny at example.com'
        ]);
        $response->assertStatus(422);
        $this->assertNull(option('teaching_office_contact_uestc'));
    }

    /** @test */
    public function changing_a_deadline_clears_the_teaching_office_notification_flag()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);

        option(["teaching_office_notified_externals_glasgow" => now()->format('Y-m-d H:i')]);

        $response = $this->actingAs($admin)->postJson(route('admin.options.update'), [
            'external_deadline_glasgow' => now()->format('d/m/Y')
        ]);

        $response->assertStatus(200);
        $this->assertEquals(0, option('teaching_office_notified_externals_uestc'));

        option(["teaching_office_notified_externals_uestc" => now()->format('Y-m-d H:i')]);

        $response = $this->actingAs($admin)->postJson(route('admin.options.update'), [
            'external_deadline_uestc' => now()->format('d/m/Y')
        ]);

        $response->assertStatus(200);
        $this->assertEquals(0, option('teaching_office_notified_externals_uestc'));
    }
}
