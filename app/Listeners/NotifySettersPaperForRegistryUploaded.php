<?php

namespace App\Listeners;

use App\Paper;
use App\Course;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaperForRegistryUploaded;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifySettersPaperForRegistryUploaded
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
        if ($event->paper->subcategory != Paper::PAPER_FOR_REGISTRY) {
            return;
        }

        $event->paper->course->setters->pluck('email')
            ->unique()
            ->each(
                fn ($email) => Mail::to($email)->later(
                    now()->addSeconds(rand(1, 200)),
                    new PaperForRegistryUploaded($event->paper->course)
                )
            );
    }
}
