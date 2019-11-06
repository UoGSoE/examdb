<?php

namespace App\Listeners;

use App\Events\PaperAdded;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\NotifyTeachingOfficeExternalHasCommented;

class NotifyTeachingOfficeThatExternalHasCommented
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
     * @param  PaperAdded  $event
     * @return void
     */
    public function handle(PaperAdded $event)
    {
        if (!request()->user()->isExternalFor($event->paper->course)) {
            return;
        }

        $contact = $event->paper->getDisciplineContact();

        Mail::to($contact)->queue(new NotifyTeachingOfficeExternalHasCommented($event->paper->course));
    }
}
