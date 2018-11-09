<?php

namespace App\Listeners;

use App\Jobs\CheckPasswordQuality;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class DispachPasswordChecker
{
    public function handle(Attempting $event)
    {
        if (config('exampapers.check_passwords')) {
            CheckPasswordQuality::dispatch($event->credentials);
        }
    }
}
