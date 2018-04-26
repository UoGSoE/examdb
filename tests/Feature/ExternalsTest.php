<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExternalsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_external_can_see_a_course_they_are_external_for()
    {
        
    }

    /** @test */
    public function an_external_cant_see_a_course_they_arent_external_for()
    {
        
    }

    /** @test */
    public function an_external_can_download_papers_for_courses_they_are_external_for()
    {
        
    }

    /** @test */
    public function an_external_cant_download_papers_for_courses_they_are_external_for()
    {
        
    }

    /** @test */
    public function an_external_can_approve_a_paper_for_courses_they_are_external_for()
    {
        
    }

    /** @test */
    public function an_external_can_approve_a_paper_only_once_the_setter_and_moderator_have_approved_them()
    {
        
    }

    /** @test */
    public function an_external_can_unapprove_a_paper()
    {
        
    }
}
