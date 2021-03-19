<?php

namespace Tests\Feature;

use App\Exceptions\PasswordQualityException;
use App\Jobs\CheckPasswordQuality;
use App\Mail\PasswordQualityFailure;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use Tests\TenantTestCase;
use Tests\TestCase;

class PasswordCheckerTest extends TenantTestCase
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
    public function a_bad_password_triggers_an_activity_log_entry_and_a_mail_to_a_sysadmin()
    {
        if (env('CI')) {
            $this->markTestSkipped('Skipping in CI');

            return;
        }
        Mail::fake();

        (new CheckPasswordQuality(['username' => 'something', 'password' => 'password']))->handle();

        Mail::assertQueued(PasswordQualityFailure::class, function ($mail) {
            return $mail->hasTo(config('exampapers.sysadmin_email')) && $mail->username === 'something';
        });
        tap(Activity::all()->last(), function ($log) {
            $this->assertEquals(
                'Password quality check for something failed. The password can not be a dictionary word., The password was found in a third party data breach, and can not be used.',
                $log->description
            );
        });
    }

    /** @test */
    public function a_strong_password_does_not_trigger_an_exception_inside_the_dispatched_job()
    {
        if (env('CI')) {
            $this->markTestSkipped('Skipping in CI');

            return;
        }

        Mail::fake();

        (new CheckPasswordQuality(['username' => 'something', 'password' => Str::random(64)]))->handle();

        Mail::assertNotQueued(PasswordQualityFailure::class);
    }
}
