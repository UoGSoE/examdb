<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogThatPaperWasAdded
{

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        activity()->causedBy($event->user)->log(
            "Uploaded a paper ({$event->paper->course->code} - {$event->paper->category} / {$event->paper->subcategory})"
        );
    }
}
