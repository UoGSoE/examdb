<?php

namespace App\Mail;

use App\Course;
use App\Paper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotifySetterAboutUnapproval extends Mailable
{
    use Queueable, SerializesModels;

    public $course;

    public $courseId;

    public $category;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(int $courseId, string $category)
    {
        $this->courseId = $courseId;
        $this->category = $category;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->course = Course::findOrFail($this->courseId);
        return $this->markdown('emails.notify_setter_unapproved');
    }
}
