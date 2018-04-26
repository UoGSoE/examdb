<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DownloadTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function users_can_only_download_papers_they_have_something_to_do_with()
    {
        
    }

    /** @test */
    public function admins_can_download_anything()
    {
        
    }
}
