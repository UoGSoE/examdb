<?php

namespace Tests\Feature;

use App\User;
use App\Tenant;
use App\Sysadmin;
use Tests\TestCase;
use Livewire\Livewire;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class SysadminTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function regular_users_cant_get_to_sysadmin_routes()
    {
        $user = Sysadmin::factory()->create(['is_sysadmin' => false]);

        $response = $this->actingAs($user, 'sysadmin')->get(route('sysadmin.dashboard'));

        $response->assertForbidden();
    }

    /** @test */
    public function we_can_get_to_the_sysadmin_login_page()
    {
        $response = $this->get(route('sysadmin.login.show'));

        $response->assertSee('ExamDB Login');
    }

    /** @test */
    public function sysadmins_can_log_in()
    {
        $this->withoutExceptionHandling();
        $sysadmin = Sysadmin::factory()->create(['username' => 'servalan', 'password' => bcrypt('swoon')]);

        $response = $this->post(route('sysadmin.login'), [
            'username' => 'servalan',
            'password' => 'swoon',
        ]);

        $response->assertSessionDoesntHaveErrors();
        $response->assertRedirect(route('sysadmin.dashboard'));
    }

    /** @test */
    public function sysadmins_cant_log_in_with_the_wrong_password()
    {
        $this->withoutExceptionHandling();
        $sysadmin = Sysadmin::factory()->create(['username' => 'servalan', 'password' => bcrypt('swoon')]);

        $response = $this->post(route('sysadmin.login'), [
            'username' => 'servalan',
            'password' => 'avon',
        ]);

        $response->assertSessionHasErrors('auth');
        $response->assertRedirect('/');
    }

    /** @test */
    public function regular_users_cant_log_in_even_with_the_right_password()
    {
        $this->withoutExceptionHandling();
        $sysadmin = Sysadmin::factory()->create(['is_sysadmin' => false, 'username' => 'servalan', 'password' => bcrypt('swoon')]);

        $response = $this->post(route('sysadmin.login'), [
            'username' => 'servalan',
            'password' => 'swoon',
        ]);

        $response->assertSessionHasErrors('auth');
        $response->assertRedirect('/');
    }

    /** @test */
    public function sysadmins_see_a_list_of_existing_tenants_when_they_login()
    {
        $admin = Sysadmin::factory()->create();
        $tenant1 = Tenant::create(['id' => 'test1']);
        $tenant1->domains()->create(['domain' => 'test1.examdb.test']);
        $tenant2 = Tenant::create(['id' => 'test2']);
        $tenant2->domains()->create(['domain' => 'test2.examdb.test']);

        $response = $this->actingAs($admin, 'sysadmin')->get(route('sysadmin.dashboard'));

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
            ->set('newName', 'foo')
            ->set('newUsername', 'fred')
            ->set('newEmail', 'fred@example.com')
            ->set('newForenames', 'Fred Regina')
            ->set('newSurname', 'Smith')
            ->call('createNew')
            ->assertHasNoErrors();

        tap(Tenant::first(), function ($tenant) use ($admin) {
            $this->assertEquals('foo.examdb.test', $tenant->domains()->first()->domain);
            $this->assertEquals($admin->username, $tenant->created_by);
            $tenant->run(function ($tenant) {
                $user = User::first();
                $this->assertEquals('fred', $user->username);
                $this->assertEquals('fred@example.com', $user->email);
                $this->assertEquals('Fred Regina', $user->forenames);
                $this->assertEquals('Smith', $user->surname);
                $this->assertTrue($user->isAdmin());
                $this->assertEquals([], $tenant->initial_user);
            });
        });
    }

    /** @test */
    public function sysadmins_cant_create_a_new_tenant_domain_with_an_existing_name()
    {
        $admin = Sysadmin::factory()->create();
        $existingTenant = Tenant::create(['id' => 'spaff']);
        $existingTenant->domains()->create(['domain' => 'spaffy.examdb.test']);

        Livewire::actingAs($admin)->test('tenant-editor')
            ->assertSee('Add new school')
            ->set('newName', 'spaffy')
            ->set('newUsername', 'fred')
            ->set('newEmail', 'fred@example.com')
            ->set('newForenames', 'Fred Regina')
            ->set('newSurname', 'Smith')
            ->call('createNew')
            ->assertHasErrors('tempNewName');

        $this->assertEquals(1, Tenant::count());
    }

    /** @test */
    public function sysadmins_can_edit_a_domain()
    {
        $admin = Sysadmin::factory()->create();
        $tenant = Tenant::create(['id' => 'glitter']);
        $tenant->domains()->create(['domain' => 'glitter.examdb.test']);

        Livewire::actingAs($admin)->test('tenant-editor')
            ->assertDontSee('Save')
            ->call('editDomain', $tenant->id)
            ->assertSee('Save')
            ->set('editingDomainName', 'whizzo.examdb.test')
            ->call('saveDomain')
            ->assertHasNoErrors();

        tap(Tenant::first(), function ($tenant) {
            $this->assertEquals('whizzo.examdb.test', $tenant->domains()->first()->domain);
        });
    }

    /** @test */
    public function sysadmins_cant_edit_a_domain_to_have_the_same_name_as_another()
    {
        $admin = Sysadmin::factory()->create();
        $tenant1 = Tenant::create(['id' => 'foobar']);
        $tenant1->domains()->create(['domain' => 'salami.examdb.test']);
        $tenant2 = Tenant::create(['id' => 'blahblah']);
        $tenant2->domains()->create(['domain' => 'blahblah.examdb.test']);

        Livewire::actingAs($admin)->test('tenant-editor')
            ->assertDontSee('Save')
            ->call('editDomain', $tenant1->id)
            ->assertSee('Save')
            ->set('editingDomainName', 'blahblah.examdb.test')
            ->call('saveDomain')
            ->assertHasErrors('editingDomainName')
            ->assertSee('The editing domain name has already been taken');

        $this->assertEquals('salami.examdb.test', $tenant1->domains()->first()->domain);
    }

    /** @test */
    public function sysadmins_can_log_into_any_domain_as_the_first_admin_user()
    {
        $admin = Sysadmin::factory()->create();
        $tenant1 = Tenant::create(['id' => 'stilton']);
        $tenant1->domains()->create(['domain' => 'stilton.examdb.test']);
        $tenant1->run(function ($tenant) {
            User::create([
                'username' => 'jenny1x',
                'email' => 'jenny@example.com',
                'password' => bcrypt(Str::random(64)),
                'forenames' => 'Jenny',
                'surname' => 'Smith',
                'is_admin' => true,
                'is_staff' => true,
            ]);
        });

        $component = Livewire::actingAs($admin)->test('tenant-editor')
            ->call('loginToTenant', $tenant1->id);

        $this->assertMatchesRegularExpression('|stilton.examdb.test/sysadmin/impersonate/.*|', $component->payload['effects']['redirect']);
    }

    /** @test */
    public function there_is_an_artisan_command_to_create_a_new_sysadmin()
    {
        $this->assertEquals(0, Sysadmin::count());

        $this->artisan('examdb:makesysadmin', [
            'username' => 'avon',
            'email' => 'avon@example.com',
            'surname' => 'Avon',
            'forename' => 'Kerr',
        ]);

        $this->assertEquals(1, Sysadmin::count());
        tap(Sysadmin::first(), function ($sysadmin) {
            $this->assertEquals('avon', $sysadmin->username);
            $this->assertEquals('avon@example.com', $sysadmin->email);
            $this->assertNotNull($sysadmin->password);
            $this->assertEquals('Avon', $sysadmin->surname);
            $this->assertEquals('Kerr', $sysadmin->forenames);
            $this->assertTrue($sysadmin->isSysadmin());
        });
    }
}
