<?php

namespace App\Listeners;

use App\Events\PaperAdded;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\NotifySetterAboutModeratorComments;

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
            abort(500, 'Need to also check that the paper subcategory is a checklist');
            $event->paper->course->setters->each(function ($setter) use ($event) {
                Mail::to($setter->email)->queue(new NotifySetterAboutModeratorComments($event->paper));
            });
        }
    }
}
