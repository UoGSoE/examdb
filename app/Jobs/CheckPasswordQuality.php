<?php

namespace App\Jobs;

use App\Mail\PasswordQualityFailure;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use LangleyFoxall\LaravelNISTPasswordRules\PasswordRules;

class CheckPasswordQuality implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $username;

    public $password;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $credentials)
    {
        if (array_key_exists('username', $credentials)) {
            $this->username = $credentials['username'];
        }
        if (array_key_exists('password', $credentials)) {
            $this->password = $credentials['password'];
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $validator = Validator::make([
            'password' => $this->password,
            'password_confirmation' => $this->password,
        ], [
            'password' => PasswordRules::register($this->username),
        ]);

        if ($validator->fails()) {
            $logMessage = 'Password quality check for '.
                            $this->username.
                            ' failed. '.
                            implode(', ', $validator->errors()->get('password'));
            activity()->log($logMessage);
            Mail::to(config('exampapers.sysadmin_email'))->queue(
                new PasswordQualityFailure($this->username, $validator->errors()->get('password'))
            );
        }
    }
}
