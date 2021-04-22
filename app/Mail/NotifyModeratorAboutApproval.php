<?php

namespace App\Mail;

use App\Course;
use App\Paper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotifyModeratorAboutApproval extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $course;

    public $courseId;

    public $category;

    public function __construct(int $courseId, string $category)
    {
        $this->courseId = $courseId;
        $this->category = $category;
    }

    public function build()
    {
        $this->courseId = Course::findOrFail($this->courseId);
        return $this->markdown('emails.notify_setter_approved');
    }
}
