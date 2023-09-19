<?php

namespace App\Listeners;

use App\Models\Course;
use App\Mail\PaperForRegistryUploaded;
use App\Models\Paper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

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
