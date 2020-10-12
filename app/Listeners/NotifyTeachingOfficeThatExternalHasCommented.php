<?php

namespace App\Listeners;

use App\Events\PaperAdded;
use App\Mail\NotifyTeachingOfficeExternalHasCommented;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

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
        if (! request()->user()->isExternalFor($event->paper->course)) {
            return;
        }

        $contact = $event->paper->getDisciplineContact();

        if (! $contact) {
            // @TODO something better...
            abort(500);
        }

        Mail::to($contact)->queue(new NotifyTeachingOfficeExternalHasCommented($event->paper->course));
    }
}
