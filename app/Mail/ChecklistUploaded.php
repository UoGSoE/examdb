<?php

namespace App\Mail;

use App\Paper;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ChecklistUploaded extends Mailable
{
    use Queueable, SerializesModels;

    public $paper;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Paper $paper)
    {
        $this->paper = $paper;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.checklist_uploaded');
    }
}
