<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Support\Str;
use App\Jobs\CheckPasswordQuality;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use App\Exceptions\PasswordQualityException;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PasswordCheckerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function when_an_admin_logs_in_a_job_is_dispatched_to_check_their_password_against_nist_guidelines_and_p0wned_if_so_configured()
    {
        if (env('CI')) {
            $this->markTestSkipped('Skipping in CI');
            return;
        }
        Queue::fake();
        config(['exampapers.check_passwords' => true]);

        $admin = create(User::class, ['is_admin' => true, 'password' => 'secret']);

        Auth::attempt(['username' => $admin->username, 'password' => 'secret']);

        Queue::assertPushed(CheckPasswordQuality::class, function ($job) use ($admin) {
            return $job->username === $admin->username && $job->password === 'secret';
        });
    }

    /** @test */
    public function when_an_admin_logs_in_a_job_is_not_dispatched_if_so_configured()
    {
        if (env('CI')) {
            $this->markTestSkipped('Skipping in CI');
            return;
        }
        Queue::fake();
        config(['exampapers.check_passwords' => false]);

        $admin = create(User::class, ['is_admin' => true, 'password' => 'secret']);

        Auth::attempt(['username' => $admin->username, 'password' => 'secret']);

        Queue::assertNotPushed(CheckPasswordQuality::class);
    }

    /** @test */
    public function when_an_admin_logs_in_a_job_is_dispatched_to_check_their_password_against_nist_guidelines_and_p0wned()
    {
        if (env('CI')) {
            $this->markTestSkipped('Skipping in CI');
            return;
        }
        Queue::fake();
        config(['exampapers.check_passwords' => true]);

        $admin = create(User::class, ['is_admin' => true, 'password' => 'secret']);

        Auth::attempt(['username' => $admin->username, 'password' => 'secret']);

        Queue::assertPushed(CheckPasswordQuality::class, function ($job) use ($admin) {
            return $job->username === $admin->username && $job->password === 'secret';
        });
    }

    /** @test */
    public function a_bad_password_triggers_an_exception_inside_the_dispatched_job()
    {
        if (env('CI')) {
            $this->markTestSkipped('Skipping in CI');
            return;
        }
        try {
            (new CheckPasswordQuality(['username' => 'something', 'password' => 'password']))->handle();
        } catch (PasswordQualityException $e) {
            $this->assertTrue(true);
            return;
        }
        $this->fail('Bad password supplied, but no exception thrown');
    }

    /** @test */
    public function a_strong_password_does_not_trigger_an_exception_inside_the_dispatched_job()
    {
        if (env('CI')) {
            $this->markTestSkipped('Skipping in CI');
            return;
        }
        try {
            (new CheckPasswordQuality(['username' => 'something', 'password' => Str::random(64)]))->handle();
            $this->assertTrue(true);
        } catch (PasswordQualityException $e) {
            $this->fail('Bad password supplied, but no exception thrown');
        }
    }
}
