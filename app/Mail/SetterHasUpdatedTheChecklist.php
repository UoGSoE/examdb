<?php

namespace App\Mail;

use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SetterHasUpdatedTheChecklist extends Mailable
{
    use Queueable, SerializesModels;

    public $course;

    public $deadline;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Course $course, string $deadline)
    {
        $this->course = $course;
        $this->deadline = $deadline;
    }

    /**
     * Build the message.
     */
    public function build(): static
    {
        return $this->markdown('emails.setter_has_updated_the_checklist');
    }
}
