<?php

namespace App\Listeners;

use App\Jobs\CheckPasswordQuality;
use Illuminate\Auth\Events\Attempting;

class DispachPasswordChecker
{
    public function handle(Attempting $event): void
    {
        if (config('exampapers.check_passwords')) {
            CheckPasswordQuality::dispatch($event->credentials);
        }
    }
}
