<?php

namespace Tests\Feature;

use App\Sysadmin;
use App\Tenant;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class SysadminTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function regular_users_cant_get_to_sysadmin_routes()
    {
        $user = Sysadmin::factory()->create(['is_sysadmin' => false]);

        $response = $this->actingAs($user)->get(route('sysadmin.dashboard'));

        $response->assertForbidden();
    }

    /** @test */
    public function sysadmins_see_a_list_of_existing_tenants_when_they_login()
    {
        $admin = Sysadmin::factory()->create();
        $tenant1 = Tenant::create(['id' => 'test1']);
        $tenant1->domains()->create(['domain' => 'test1.examdb.test']);
        $tenant2 = Tenant::create(['id' => 'test2']);
        $tenant2->domains()->create(['domain' => 'test2.examdb.test']);

        $response = $this->actingAs($admin)->get(route('sysadmin.dashboard'));

        $response->assertSee('test1.examdb.test');
        $response->assertSee('test2.examdb.test');
        $response->assertSeeLivewire('tenant-editor');
    }

    /** @test */
    public function sysadmins_can_create_a_new_tenant_domain()
    {
        $admin = Sysadmin::factory()->create();

        Livewire::actingAs($admin)->test('tenant-editor')
            ->assertSee('Add new school')
            ->set('newName', 'foobar')
            ->call('createNew')
            ->assertHasNoErrors();

        tap(Tenant::first(), function ($tenant) {
            $this->assertEquals('foobar.examdb.test', $tenant->domains()->first()->domain);
        });
    }

    /** @test */
    public function sysadmins_can_edit_a_domain()
    {
        $admin = Sysadmin::factory()->create();
        $tenant = Tenant::create(['id' => 'foobar']);
        $tenant->domains()->create(['domain' => 'foobar.examdb.test']);

        Livewire::actingAs($admin)->test('tenant-editor')
            ->assertDontSee('Save')
            ->call('editDomain', $tenant->id)
            ->assertSee('Save')
            ->set('editingDomainName', 'whizzo')
            ->call('saveDomain')
            ->assertHasNoErrors();

        tap(Tenant::first(), function ($tenant) {
            $this->assertEquals('whizzo.examdb.test', $tenant->domains()->first()->domain);
        });
    }

}
