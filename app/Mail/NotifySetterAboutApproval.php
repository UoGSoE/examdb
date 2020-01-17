<?php

namespace App\Mail;

use App\User;
use App\Paper;
use App\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifySetterAboutApproval extends Mailable
{
    use Queueable, SerializesModels;

    public $course;

    public $category;

    public $user;

    public $userType;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Course $course, string $category, User $user)
    {
        $this->course = $course;
        $this->category = $category;
        $this->user = $user;
        $this->userType = $user->isModeratorFor($course) ? 'moderator' : 'external';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.notify_moderator_approved');
    }
}
