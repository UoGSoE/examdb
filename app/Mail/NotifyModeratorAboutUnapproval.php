<?php

namespace App\Mail;

use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotifyModeratorAboutUnapproval extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $course;

    public $category;

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
        return $this->markdown('emails.notify_moderator_unapproved');
    }
}
