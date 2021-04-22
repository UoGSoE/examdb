<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExternalLoginUrl extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public $userId;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->user = User::findOrFail($this->userId);
        return $this->markdown('emails.external.login');
    }
}
