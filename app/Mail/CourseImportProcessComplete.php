<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CourseImportProcessComplete extends Mailable
{
    use Queueable, SerializesModels;

    public array $errors = [];

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $errors)
    {
        $this->errors = $errors;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.course_import_process_complete');
    }
}
