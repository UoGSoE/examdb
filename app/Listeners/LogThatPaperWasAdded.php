<?php

namespace App\Listeners;

use App\Paper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogThatPaperWasAdded
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $prefix = 'Uploaded a paper';
        if ($event->paper->subcategory == Paper::COMMENT_SUBCATEGORY) {
            $prefix = 'Added a comment';
        }
        activity()->causedBy($event->user)->log(
            "{$prefix} ({$event->paper->course->code} - {$event->paper->category} / {$event->paper->subcategory})"
        );
    }
}
