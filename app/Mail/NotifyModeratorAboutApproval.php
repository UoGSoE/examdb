<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Course;

class NotifyModeratorAboutApproval extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $course;

    public $category;

    public function __construct(Course $course, string $category)
    {
        $this->course = $course;
        $this->category = $category;
    }

    public function build()
    {
        return $this->markdown('emails.notify_setter_approved');
    }
}
