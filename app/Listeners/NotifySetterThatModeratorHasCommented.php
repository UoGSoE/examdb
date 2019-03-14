<?php

namespace App\Listeners;

use App\Events\PaperAdded;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotifySetterAboutUpload;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifySetterThatModeratorHasCommented
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
        if (request()->user()->isModeratorFor($event->paper->course)) {
            $event->paper->course->setters->each(function ($setter) use ($event) {
                Mail::to($setter->email)->queue(new NotifySetterAboutUpload($event->paper));
            });
        }
    }
}
