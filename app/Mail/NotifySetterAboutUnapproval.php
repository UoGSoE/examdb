<?php

namespace App\Mail;

use App\Paper;
use App\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifySetterAboutUnapproval extends Mailable
{
    use Queueable, SerializesModels;

    public $course;

    public $category;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Course $course, string $category)
    {
        $this->course = $course;
        $this->category = $category;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.notify_setter_unapproved');
    }
}
