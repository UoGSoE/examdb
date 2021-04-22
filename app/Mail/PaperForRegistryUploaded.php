<?php

namespace App\Mail;

use App\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaperForRegistryUploaded extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $course;

    public $courseId;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(int $courseId)
    {
        $this->courseId = $courseId;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->course = Course::findOrFail($this->courseId);
        return $this->markdown('emails.paper_for_registry_uploaded');
    }
}
