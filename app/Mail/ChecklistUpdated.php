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

    public $checklistId;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(int $checklistId)
    {
        $this->checklistId = $checklistId;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->checklist = PaperChecklist::findOrFail($this->checklistId);
        return $this->markdown('emails.checklist_updated');
    }
}
