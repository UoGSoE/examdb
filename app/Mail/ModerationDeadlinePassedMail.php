<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ModerationDeadlinePassedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $deadline;
    public $courses;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Carbon $deadline, array $courses)
    {
        $this->deadline = $deadline;
        $this->courses = collect($courses);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.moderation_deadline_passed');
    }
}
