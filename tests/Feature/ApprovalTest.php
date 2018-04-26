<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApprovalTest extends TestCase
{
    use RefreshDatabase;


    /** @test */
    public function an_external_can_approve_or_unapprove_a_main_paper_only_if_the_setter_and_moderator_have_approved_it()
    {
        
    }

}
