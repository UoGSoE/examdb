<?php

namespace App\Mail;

use App\Paper;
use App\PaperChecklist;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ChecklistUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $checklist;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(PaperChecklist $checklist)
    {
        $this->checklist = $checklist;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.checklist_updated');
    }
}
