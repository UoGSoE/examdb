<?php

namespace App\Console\Commands;

use App\Course;
use App\Mail\CallForPapersMail;
use App\Mail\ExternalModerationDeadlineMail;
use App\Mail\ModerationDeadlineMail;
use App\Mail\ModerationDeadlinePassedMail;
use App\Mail\NotifyExternalsReminderMail;
use App\Mail\PrintReadyDeadlineMail;
use App\Mail\PrintReadyDeadlinePassedMail;
use App\Mail\SubmissionDeadlineMail;
use App\Mail\SubmissionDeadlinePassedMail;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TimedNotifications extends Command
{
    protected $signature = 'examdb:timed-notifications';

    protected $description = 'Send any automatic notifications that are due';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->handleCallForPapers();
        $this->handleSubmissionDeadline('glasgow');
        $this->handleSubmissionDeadline('uestc');
        $this->handleModerationDeadline('glasgow');
        $this->handleModerationDeadline('uestc');
        $this->handleNotifyExternalsReminder('glasgow');
        $this->handleNotifyExternalsReminder('uestc');
        $this->handleExternalModerationDeadline('glasgow');
        $this->handleExternalModerationDeadline('uestc');
        $this->handlePrintReadyDeadline('glasgow');
        $this->handlePrintReadyDeadline('uestc');
    }

    protected function handleCallForPapers()
    {
        if (! option('date_receive_call_for_papers')) {
            $this->info('Skipping call for papers email as no date set');

            return;
        }
        try {
            $date = Carbon::parse(option('date_receive_call_for_papers'));
        } catch (Exception $e) {
            $this->info('Could not parse date_receive_call_for_papers');

            return;
        }

        if ($date->dayOfYear > now()->dayOfYear) {
            return;
        }

        if (option('date_receive_call_for_papers_email_sent')) {
            return;
        }

        $emailAddresses = Course::with('setters')->get()->flatMap(function ($course) {
            return $course->setters->pluck('email');
        })->filter()->unique();

        $emailAddresses->each(function ($email) {
            Mail::to($email)->later(now()->addSeconds(rand(1, 200)), new CallForPapersMail);
        });

        option(['date_receive_call_for_papers_email_sent' => now()->format('Y-m-d')]);
    }

    protected function handleSubmissionDeadline(string $area)
    {
        $optionName = "{$area}_staff_submission_deadline";
        if (! option($optionName)) {
            $this->info('Skipping submission deadline email as no date set');

            return;
        }
        try {
            $date = Carbon::parse(option($optionName));
        } catch (Exception $e) {
            $this->info("Could not parse $optionName");

            return;
        }

        if ($date->dayOfYear != now()->subDay()->dayOfYear && $date->dayOfYear != now()->addWeek()->dayOfYear) {
            return;
        }

        if (option("{$optionName}_email_sent")) {
            return;
        }

        $emailAddresses = collect([]);
        if ($date->clone()->subWeek()->dayOfYear == now()->dayOfYear) {
            $mailableName = SubmissionDeadlineMail::class;
            $emailAddresses = $this->getAllSetterEmails($area);
        } else {
            $mailableName = SubmissionDeadlinePassedMail::class;
            $emailAddresses = $this->getIncompletePaperworkSetterEmails($area);
        }

        $emailAddresses->each(function ($email) use ($mailableName) {
            Mail::to($email)->later(now()->addSeconds(rand(1, 200)), new $mailableName);
        });

        if ($date->addDay()->dayOfYear == now()->dayOfYear) {
            option(["{$optionName}_email_sent" => now()->format('Y-m-d')]);
        }
    }

    protected function handleModerationDeadline(string $area)
    {
        $optionName = "{$area}_internal_moderation_deadline";
        if (! option($optionName)) {
            $this->info('Skipping moderation deadline email as no date set');

            return;
        }
        try {
            $date = Carbon::parse(option($optionName));
        } catch (Exception $e) {
            $this->info("Could not parse $optionName");

            return;
        }

        if ($date->dayOfYear != now()->subDay()->dayOfYear && $date->dayOfYear != now()->addDays(3)->dayOfYear) {
            return;
        }
        if (option("{$optionName}_email_sent")) {
            return;
        }

        if ($date->clone()->subDays(3)->dayOfYear == now()->dayOfYear) {
            $mailableName = ModerationDeadlineMail::class;
            $emailAddresses = $this->getAllModeratorEmails($area);
        } else {
            $mailableName = ModerationDeadlinePassedMail::class;
            $emailAddresses = $this->getIncompletePaperworkModeratorEmails($area);
        }

        $emailAddresses->each(function ($email) use ($mailableName) {
            Mail::to($email)->later(now()->addSeconds(rand(1, 200)), new $mailableName);
        });

        if ($date->addDay()->dayOfYear == now()->dayOfYear) {
            option(["{$optionName}_email_sent" => now()->format('Y-m-d')]);
        }
    }

    protected function handleNotifyExternalsReminder(string $area)
    {
        $optionName = "date_remind_{$area}_office_externals";
        if (! option($optionName)) {
            $this->info('Skipping reminder about externals email as no date set');

            return;
        }
        try {
            $date = Carbon::parse(option($optionName));
        } catch (Exception $e) {
            $this->info("Could not parse $optionName");

            return;
        }

        if ($date->dayOfYear != now()->dayOfYear) {
            return;
        }

        if (option("{$optionName}_email_sent")) {
            return;
        }

        Mail::to(option("teaching_office_contact_{$area}"))
            ->later(now()->addSeconds(rand(1, 200)), new NotifyExternalsReminderMail);

        option(["{$optionName}_email_sent" => now()->format('Y-m-d')]);
    }

    protected function handlePrintReadyDeadline(string $area)
    {
        $optionName = "{$area}_print_ready_deadline";
        if (! option($optionName)) {
            $this->info('Skipping print ready deadline email as no date set');

            return;
        }
        try {
            $date = Carbon::parse(option($optionName));
        } catch (Exception $e) {
            $this->info("Could not parse $optionName");

            return;
        }

        if ($date->dayOfYear != now()->subDay()->dayOfYear && $date->dayOfYear != now()->addDay()->dayOfYear) {
            return;
        }
        if (option("{$optionName}_email_sent")) {
            return;
        }

        if ($date->clone()->subDay()->dayOfYear == now()->dayOfYear) {
            $mailableName = PrintReadyDeadlineMail::class;
        } else {
            $mailableName = PrintReadyDeadlinePassedMail::class;
        }

        Mail::to(option("teaching_office_contact_{$area}"))
            ->later(now()->addSeconds(rand(1, 200)), new $mailableName);

        if ($date->addDay()->dayOfYear == now()->dayOfYear) {
            option(["{$optionName}_email_sent" => now()->format('Y-m-d')]);
        }
    }

    protected function handleExternalModerationDeadline(string $area)
    {
        $optionName = "{$area}_external_moderation_deadline";
        if (! option($optionName)) {
            $this->info('Skipping reminder about external moderation email as no date set');

            return;
        }
        try {
            $date = Carbon::parse(option($optionName));
        } catch (Exception $e) {
            $this->info("Could not parse $optionName");

            return;
        }

        if ($date->dayOfYear != now()->dayOfYear) {
            return;
        }

        if (option("{$optionName}_email_sent")) {
            return;
        }

        Mail::to(option("teaching_office_contact_{$area}"))
            ->later(now()->addSeconds(rand(1, 200)), new ExternalModerationDeadlineMail);

        option(["{$optionName}_email_sent" => now()->format('Y-m-d')]);
    }

    protected function getAllSetterEmails(string $area)
    {
        return Course::forArea($area)->with('setters')->get()->flatMap(function ($course) {
            return $course->setters->pluck('email');
        })->filter()->unique();
    }

    protected function getIncompletePaperworkSetterEmails(string $area)
    {
        return Course::forArea($area)->doesntHave('checklists')->with('setters')->get()->flatMap(function ($course) {
            return $course->setters->pluck('email');
        })->filter()->unique();
    }

    protected function getAllModeratorEmails(string $area)
    {
        return Course::forArea($area)->with('moderators')->get()->flatMap(function ($course) {
            return $course->moderators->pluck('email');
        })->filter()->unique();
    }

    protected function getIncompletePaperworkModeratorEmails(string $area)
    {
        return Course::forArea($area)->with('moderators')
            ->where('moderator_approved_main', '!=', true)
            ->orWhere('moderator_approved_resit', '!=', true)
            ->get()
            ->flatMap(function ($course) {
                return $course->moderators->pluck('email');
            })->filter()->unique();
    }
}