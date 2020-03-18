<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotifyTeachingOfficeExternalDeadline extends Mailable
{
    use Queueable, SerializesModels;

    public $area;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $area)
    {
        $this->area = $area;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.notify_teaching_office_external_deadline');
    }
}
