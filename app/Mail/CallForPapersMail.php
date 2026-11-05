<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CallForPapersMail extends Mailable
{
    use Queueable, SerializesModels;

    public $deadlineGlasgow;

    public $deadlineUestc;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Carbon $deadlineGlasgow, Carbon $deadlineUestc)
    {
        $this->deadlineGlasgow = $deadlineGlasgow;
        $this->deadlineUestc = $deadlineUestc;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Call for Exam Papers')->markdown('emails.call_for_papers');
    }
}
