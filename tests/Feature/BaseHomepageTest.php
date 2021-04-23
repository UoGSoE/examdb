<?php

namespace Tests\Feature;

use App\Tenant;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BaseHomepageTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function when_someone_visits_the_base_url_they_get_a_list_of_tenants_to_log_into()
    {
        $tenant1 = Tenant::create(['id' => 'splash']);
        $tenant1->domains()->create(['domain' => 'splash.examdb.test']);
        $tenant2 = Tenant::create(['id' => 'drip']);
        $tenant2->domains()->create(['domain' => 'drip.examdb.test']);

        $response = $this->get(route('base.homepage'));

        $response->assertSee("splash.examdb.test");
        $response->assertSee("drip.examdb.test");
    }

}
