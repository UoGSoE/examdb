<?php

namespace App\Listeners;

use App\Events\PaperUnapproved;
use App\Mail\NotifySetterAboutUnapproval;
use Illuminate\Support\Facades\Mail;

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
     */
    public function handle(PaperUnapproved $event): void
    {
        activity()->causedBy($event->user)->log(
            "Unapproved {$event->category} paper for {$event->course->code}"
        );

        $event->course->setters->each(function ($setter) use ($event) {
            Mail::to($setter)->queue(new NotifySetterAboutUnapproval($event->course, $event->category));
        });
    }
}
