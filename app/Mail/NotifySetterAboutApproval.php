<?php

namespace App\Mail;

use App\Course;
use App\Paper;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotifySetterAboutApproval extends Mailable
{
    use Queueable, SerializesModels;

    public $course;

    public $courseId;

    public $category;

    public $user;

    public $userId;

    public $userType;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(int $courseId, string $category, int $userId)
    {
        $this->courseId = $courseId;
        $this->category = $category;
        $this->userId = $userId;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->course = Course::findOrFail($this->courseId);
        $this->user = User::findOrFail($this->userId);
        $this->userType = $this->user->isModeratorFor($this->course) ? 'moderator' : 'external';
        return $this->markdown('emails.notify_moderator_approved');
    }
}
