<?php

namespace Tests\Feature\Admin;

use App\User;
use Tests\TestCase;
use Livewire\Livewire;
use Tests\TenantTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class OptionsTest extends TenantTestCase
{


    /** @test */
    public function regular_users_cant_see_the_admin_options_page()
    {
        $user = create(User::class);

        $response = $this->actingAs($user)->get(route('admin.options.edit'));

        $response->assertStatus(403);
    }

    /** @test */
    public function admins_can_see_the_admin_options_page()
    {
        $admin = create(User::class, ['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.options.edit'));

        $response->assertStatus(200);
        $response->assertViewHas('options');
        $response->assertSeeLivewire('options-editor');
    }

    /** @test */
    public function admins_can_set_the_system_options()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);

        option(['date_recieve_call_for_papers' => '2020-01-03']);

        Livewire::actingAs($admin)
            ->test('options-editor')
            ->set('options.date_receive_call_for_papers', now()->format('d/m/Y'))
            ->set('options.staff_submission_deadline', now()->format('d/m/Y'))
            ->set('options.internal_moderation_deadline', now()->format('d/m/Y'))
            ->set('options.date_remind_office_externals', now()->format('d/m/Y'))
            ->set('options.external_moderation_deadline', now()->format('d/m/Y'))
            ->set('options.print_ready_deadline', now()->format('d/m/Y'))
            ->set('options.teaching_office_contact', 'jane@example.com')
            ->set('options.start_semester_1', now()->format('d/m/Y'))
            ->set('options.start_semester_2', now()->format('d/m/Y'))
            ->set('options.start_semester_3', now()->format('d/m/Y'))
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals(now()->format('Y-m-d'), option('date_receive_call_for_papers'));
        $this->assertEquals('jane@example.com', option('teaching_office_contact'));
        $this->assertEquals(now()->format('Y-m-d'), option('internal_moderation_deadline'));
    }

    /** @test */
    public function options_have_to_be_in_valid_formats()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);

        // external deadlines
        option(['date_receive_call_for_papers' => '2020-01-04']);

        Livewire::actingAs($admin)
            ->test('options-editor')
            ->set('options.date_receive_call_for_papers', 'not a date')
            ->set('options.staff_submission_deadline', 'not a date')
            ->set('options.internal_moderation_deadline', 'not a date')
            ->set('options.date_remind_office_externals', 'not a date')
            ->set('options.external_moderation_deadline', 'not a date')
            ->set('options.print_ready_deadline', 'not a date')
            ->set('options.teaching_office_contact', 'not an email address')
            ->call('save')
            ->assertHasErrors();
    }

    /** @test */
    public function changing_a_deadline_clears_the_teaching_office_notification_flag()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);

        option(['date_receive_call_for_papers_email_sent' => now()->subDays(3)->format('Y-m-d H:i')]);
        option(['staff_submission_deadline_email_sent_upcoming_semester_1' => now()->subDays(3)->format('Y-m-d H:i')]);

        Livewire::actingAs($admin)
            ->test('options-editor')
            ->set('options.date_receive_call_for_papers', now()->format('d/m/Y'))
            ->set('options.staff_submission_deadline', now()->format('d/m/Y'))
            ->set('options.internal_moderation_deadline', now()->format('d/m/Y'))
            ->set('options.date_remind_office_externals', now()->format('d/m/Y'))
            ->set('options.external_moderation_deadline', now()->format('d/m/Y'))
            ->set('options.print_ready_deadline', now()->format('d/m/Y'))
            ->set('options.teaching_office_contact', 'jane@example.com')
            ->set('options.start_semester_1', now()->format('d/m/Y'))
            ->set('options.start_semester_2', now()->format('d/m/Y'))
            ->set('options.start_semester_3', now()->format('d/m/Y'))
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals(0, option('date_receive_call_for_papers_email_sent'));
        $this->assertEquals(0, option('staff_submission_deadline_email_sent_upcoming_semester_1'));
    }
}
