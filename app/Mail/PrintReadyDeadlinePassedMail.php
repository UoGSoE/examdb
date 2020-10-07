<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Collection;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PrintReadyDeadlinePassedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $deadline;
    public $courses;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Carbon $deadline, Collection $courses)
    {
        $this->deadline = $deadline;
        $this->courses = $courses;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.print_ready_deadline_passed');
    }
}
