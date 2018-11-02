<?php

namespace App\Listeners;

use App\Events\PaperUnapproved;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class PaperWasUnapproved
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  PaperUnapproved  $event
     * @return void
     */
    public function handle(PaperUnapproved $event)
    {
        activity()->causedBy($event->user)->log(
            "Unapproved {$event->category} paper for {$event->course->code}"
        );
    }
}
