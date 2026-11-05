<?php

namespace App\Mail;

use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

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
