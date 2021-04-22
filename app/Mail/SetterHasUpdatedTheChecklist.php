<?php

namespace App\Mail;

use App\Course;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SetterHasUpdatedTheChecklist extends Mailable
{
    use Queueable, SerializesModels;

    public $course;

    public $couseId;

    public $deadline;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(int $courseId, string $deadline)
    {
        $this->courseId = $courseId;
        $this->deadline = $deadline;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->course = Course::findOrFail($this->courseId);
        return $this->markdown('emails.setter_has_updated_the_checklist');
    }
}
