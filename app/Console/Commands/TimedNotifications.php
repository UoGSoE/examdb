<?php

namespace App\Console\Commands;

use App\Course;
use App\Exceptions\TimedNotificationException;
use App\Jobs\NotificationChecks;
use App\Mail\CallForPapersMail;
use App\Mail\ExternalModerationDeadlineMail;
use App\Mail\ModerationDeadlineMail;
use App\Mail\ModerationDeadlinePassedMail;
use App\Mail\NotifyExternalsReminderMail;
use App\Mail\PrintReadyDeadlineMail;
use App\Mail\PrintReadyDeadlinePassedMail;
use App\Mail\SubmissionDeadlineMail;
use App\Mail\SubmissionDeadlinePassedMail;
use App\Paper;
use App\Tenant;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class TimedNotifications extends Command
{
    protected $signature = 'examdb:timed-notifications';

    protected $description = 'Send any automatic notifications that are due';

    protected $exceptions = [];

    protected $semester;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        Tenant::all()->each(fn ($tenant) => NotificationChecks::dispatch($tenant->id));
    }
}
