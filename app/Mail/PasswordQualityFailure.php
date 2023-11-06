<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordQualityFailure extends Mailable
{
    use Queueable, SerializesModels;

    public $username;

    public $errors;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $username, array $errors)
    {
        $this->username = $username;
        $this->errors = $errors;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        return $this->markdown('emails.password_quality');
    }
}
