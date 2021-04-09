<?php

namespace Tests;

use App\Tenant;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

abstract class TenantTestCase extends TestCase
{
    use DatabaseMigrations;

    /**
     * Create tenant and initialize tenancy?
     *
     * @var boolean
     */
    protected $tenancy = true;
    protected $shouldSeed = true;

    public function setUp(): void
    {
        parent::setUp();

        if (! $this->shouldSeed) {
            config(['tenancy.seeder_parameters.--class' => EmptySeeder::class]);
        }

        if ($this->tenancy) {
            $tenant = $this->createTenant([], 'tenant');
            tenancy()->initialize($tenant);

            config(['app.url' => 'http://tenant.localhost']);

            /** @var UrlGenerator */
            $urlGenerator = url();
            $urlGenerator->forceRootUrl('http://tenant.localhost');

            $this->withServerVariables([
                'SERVER_NAME' => 'tenant.localhost',
                'HTTP_HOST' => 'tenant.localhost',
            ]);

            // Login as superuser
            // auth()->loginUsingId(1);
        }
    }

    public function tearDown(): void
    {
        Tenant::all()->each->delete();
    }
}
