<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ChecklistsReadyToDownload extends Mailable
{
    use Queueable, SerializesModels;

    public $link;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $link)
    {
        $this->link = $link;
    }

    /**
     * Build the message.
     */
    public function build(): static
    {
        return $this->markdown('emails.checklists_ready_to_download');
    }
}
