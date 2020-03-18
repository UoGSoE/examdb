<?php

namespace App\Listeners;

use App\Jobs\CheckPasswordQuality;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DispachPasswordChecker
{
    public function handle(Attempting $event)
    {
        if (config('exampapers.check_passwords')) {
            CheckPasswordQuality::dispatch($event->credentials);
        }
    }
}
