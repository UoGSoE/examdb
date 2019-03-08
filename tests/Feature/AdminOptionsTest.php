<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminOptionsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function regular_users_cant_see_the_admin_options_page()
    {
        // @TODO
    }

    /** @test */
    public function admins_can_see_the_admin_options_page()
    {
        // @TODO
    }

    /** @test */
    public function admins_can_set_the_system_options()
    {
        // @TODO
    }
}
