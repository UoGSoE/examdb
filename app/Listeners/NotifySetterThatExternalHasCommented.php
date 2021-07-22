<?php

namespace App\Listeners;

use App\Events\PaperAdded;
use App\Mail\NotifySetterAboutExternalComments;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class NotifySetterThatExternalHasCommented
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
    public function handle(PaperAdded $event)
    {
        if (! request()->user()->isExternalFor($event->paper->course)) {
            return;
        }

        $event->paper->course->setters->each(function ($setter) use ($event) {
            Mail::to($setter->email)->queue(new NotifySetterAboutExternalComments($event->paper->course->id));
        });
    }
}
