<?php

namespace App\Listeners;

use App\Events\PaperAdded;
use App\Mail\PaperForRegistry;
use App\Models\Paper;
use Illuminate\Support\Facades\Mail;

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
     * @return void
     */
    public function handle(PaperAdded $event): void
    {
        if ($event->paper->subcategory != Paper::PAPER_FOR_REGISTRY) {
            return;
        }

        $contact = $event->paper->getDisciplineContact();
        if (! $contact) {
            // @TODO something better...
            abort(500, 'No contact email address found');
        }
        Mail::to($contact)->queue(new PaperForRegistry($event->paper->course));
    }
}
