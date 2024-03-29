<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use App\AcademicSession;
use App\Mail\ExternalLoginUrl;
use Illuminate\Support\Facades\Mail;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExternalUsersLoginTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        AcademicSession::createFirstSession();
    }

    /** @test */
    public function an_external_can_be_emailed_a_time_limited_signed_login_url()
    {
        $this->withoutExceptionHandling();
        Mail::fake();
        $external = User::factory()->external()->create();

        $response = $this->post(route('external-generate-login'), [
            'email' => $external->email,
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('success');
        Mail::assertQueued(ExternalLoginUrl::class, function ($mail) use ($external) {
            return $mail->hasTo($external->email);
        });
        // and check we recorded this in the activity/audit log
        tap(Activity::all()->last(), function ($log) use ($external) {
            $this->assertTrue($log->causer->is($external));
            $this->assertEquals('External asked for login url', $log->description);
        });
    }

    /** @test */
    public function when_an_external_asks_for_a_login_url_the_email_address_is_case_insensitive()
    {
        $this->withoutExceptionHandling();
        Mail::fake();
        $external = User::factory()->external()->create(['username' => 'external@example.com']);

        $response = $this->post(route('external-generate-login'), [
            'email' => 'EXTERNAL@EXAMPLE.COM',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('success');
        Mail::assertQueued(ExternalLoginUrl::class, function ($mail) use ($external) {
            return $mail->hasTo($external->email);
        });
        // and check we recorded this in the activity/audit log
        tap(Activity::all()->last(), function ($log) use ($external) {
            $this->assertTrue($log->causer->is($external));
            $this->assertEquals('External asked for login url', $log->description);
        });
    }

    /** @test */
    public function when_an_email_is_entered_that_doesnt_match_an_external_then_no_email_is_sent()
    {
        $this->withoutExceptionHandling();
        Mail::fake();

        $response = $this->post(route('external-generate-login'), [
            'email' => 'blah@example.com',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('success');
        Mail::assertNotQueued(ExternalLoginUrl::class);

        // and check we recorded this in the activity/audit log
        tap(Activity::all()->last(), function ($log) {
            $this->assertEquals('External asked for login url - but no matching email address blah@example.com', $log->description);
        });
    }

    /** @test */
    public function when_an_external_visits_a_valid_signed_login_url_they_are_logged_in()
    {
        $this->withoutExceptionHandling();
        $external = User::factory()->external()->create();

        $response = $this->get($external->generateLoginUrl());

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($external);

        // and check we recorded this in the activity/audit log
        tap(Activity::all()->last(), function ($log) use ($external) {
            $this->assertTrue($log->causer->is($external));
            $this->assertEquals('Logged in from IP 127.0.0.1', $log->description);
        });
    }

    /** @test */
    public function when_an_external_visits_a_valid_signed_login_url_that_has_expired_they_are_not_logged_in()
    {
        $external = User::factory()->external()->create();
        // set the url signature timeout in the past...
        config(['exampapers.login_link_minutes' => -5]);

        $response = $this->get($external->generateLoginUrl());

        $response->assertStatus(403);
        $this->assertFalse(\Auth::check());

        // and check we recorded this in the activity/audit log
        tap(Activity::all()->last(), function ($log) use ($external) {
            $this->assertEquals('External tried to use a expired or invalid login url from IP 127.0.0.1', $log->description);
        });
    }

    /** @test */
    public function when_an_external_visits_an_invalid_login_url_they_are_not_logged_in()
    {
        $external = User::factory()->external()->create();

        $response = $this->get($external->generateLoginUrl().'invalid');

        $response->assertStatus(403);
        $this->assertFalse(\Auth::check());

        // and check we recorded this in the activity/audit log
        tap(Activity::all()->last(), function ($log) use ($external) {
            $this->assertEquals('External tried to use a expired or invalid login url from IP 127.0.0.1', $log->description);
        });
    }
}
