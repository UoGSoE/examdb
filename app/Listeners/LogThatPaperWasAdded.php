<?php

namespace App\Listeners;

use App\Models\Paper;

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
    public function handle(object $event): void
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
