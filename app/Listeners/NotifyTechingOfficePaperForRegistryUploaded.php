<?php

namespace App\Listeners;

use App\Events\PaperAdded;
use App\Mail\PaperForRegistry;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyTechingOfficePaperForRegistryUploaded
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
        if ($event->paper->subcategory != 'Paper For Registry') {
            return;
        }

        $contact = $event->paper->getDisciplineContact();
        if (!$contact) {
            // @TODO something better...
            abort(500);
        }
        Mail::to($contact)->queue(new PaperForRegistry($event->paper->course));
    }
}
