<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaperUploadTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_setter_can_upload_a_paper()
    {
        // @TODO
    }

    /** @test */
    public function when_the_setter_uploads_the_checklist_a_mail_is_triggered_to_the_moderators()
    {
        // @TODO

    }

    /** @test */
    public function a_moderator_can_upload_thier_comments_which_triggers_an_email_to_the_setter()
    {
        // @TODO

    }

    /** @test */
    public function when_the_setter_uploads_the_paper_for_registry_an_email_is_sent_to_teaching_office_contact()
    {
        // @TODO

    }
}
