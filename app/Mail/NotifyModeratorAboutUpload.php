<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Paper;

class NotifyModeratorAboutUpload extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $paper;

    public function __construct(Paper $paper)
    {
        $this->paper = $paper;
    }

    public function build()
    {
        return $this->markdown('emails.notify_moderator');
    }
}
