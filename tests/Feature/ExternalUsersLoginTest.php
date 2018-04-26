<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Mail\ExternalLoginUrl;

class ExternalUsersLoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function when_an_external_logs_in_they_are_emailed_a_time_limited_signed_login_url()
    {
        $this->withoutExceptionHandling();
        Mail::fake();
        $external = factory(User::class)->states('external')->create();

        $response = $this->post(route('external-generate-login'), [
            'email' => $external->email,
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('success');
        Mail::assertQueued(ExternalLoginUrl::class, function ($mail) use ($external) {
            return $mail->hasTo($external->email);
        });
    }

    /** @test */
    public function when_an_external_visits_a_valid_signed_login_url_they_are_logged_in()
    {
        $this->withoutExceptionHandling();
        $external = factory(User::class)->states('external')->create();

        $response = $this->get($external->generateLoginUrl());

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($external);
    }

    /** @test */
    public function when_an_external_visits_a_valid_signed_login_url_that_has_expired_they_are_not_logged_in()
    {
        $external = factory(User::class)->states('external')->create();
        // set the url signature timeout in the past...
        config(['exampapers.login_link_minutes' => -5]);

        $response = $this->get($external->generateLoginUrl());

        $response->assertStatus(403);
        $this->assertFalse(\Auth::check());
    }

    /** @test */
    public function when_an_external_visits_an_invalid_login_url_they_are_not_logged_in()
    {
        $external = factory(User::class)->states('external')->create();

        $response = $this->get($external->generateLoginUrl() . 'invalid');

        $response->assertStatus(403);
        $this->assertFalse(\Auth::check());
    }
}
