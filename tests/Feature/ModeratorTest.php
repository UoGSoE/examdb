<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ModeratorTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_moderator_can_view_a_course_they_are_moderating()
    {
    }

    /** @test */
    public function a_moderator_cant_view_a_course_they_are_not_moderating()
    {
        
    }

    /** @test */
    public function a_moderator_can_add_a_comments_file_to_a_main_paper()
    {
        
    }

    /** @test */
    public function a_moderator_can_add_a_comments_file_to_a_resit_paper()
    {
        
    }

    /** @test */
    public function a_moderator_can_approve_a_paper()
    {
        
    }

    /** @test */
    public function a_moderator_can_approve_a_main_paper_only_if_the_setter_has_approved_it()
    {
        
    }

    /** @test */
    public function a_moderator_can_unapprove_a_paper()
    {
        
    }

    /** @test */
    public function an_admin_can_do_anything_regarding_moderating()
    {
        
    }
}
