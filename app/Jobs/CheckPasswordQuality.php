<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Exceptions\PasswordQualityException;
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
     *
     * @return void
     */
    public function handle()
    {
        $validator = Validator::make([
            'password' => $this->password,
            'password_confirmation' => $this->password,
        ], [
            'password' => PasswordRules::register($this->username)
        ]);

        if ($validator->fails()) {
            throw new PasswordQualityException("Username {$this->username} - " . implode(", ", $validator->errors()->all()));
        }
    }
}