<?php

namespace App\Listeners;

use App\Events\PaperAdded;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyAboutNewPaper
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
        return true;
        if ($event->user->isSetterFor($event->course)) {
            // notify moderator(s)
        }
        if ($event->user->isModeratorFor($event->course)) {
            // notify setter(s)
        }
        if ($event->user->isExternal()) {
            // notify moderator(s) and setter(s)
        }
    }
}
