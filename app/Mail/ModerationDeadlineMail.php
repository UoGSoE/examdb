<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ModerationDeadlineMail extends Mailable
{
    use Queueable, SerializesModels;

    public $deadline;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Carbon $deadline)
    {
        $this->deadline = $deadline;
    }

    /**
     * Build the message.
     */
    public function build(): static
    {
        return $this->markdown('emails.moderation_deadline');
    }
}
