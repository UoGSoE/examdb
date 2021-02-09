<?php

namespace App\Console\Commands;

use App\Course;
use App\Exceptions\TimedNotificationException;
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
        $this->semester = $this->getCurrentSemester();

        // :: sad face ::
        try {
            $this->handleCallForPapers();
        } catch (\Exception $e) {
            $this->exceptions[] = $e;
        }

        try {
            $this->handleSubmissionDeadline('glasgow');
        } catch (\Exception $e) {
            $this->exceptions[] = $e;
        }

        try {
            $this->handleSubmissionDeadline('uestc');
        } catch (\Exception $e) {
            $this->exceptions[] = $e;
        }

        try {
            $this->handleModerationDeadline('glasgow');
        } catch (\Exception $e) {
            $this->exceptions[] = $e;
        }

        try {
            $this->handleModerationDeadline('uestc');
        } catch (\Exception $e) {
            $this->exceptions[] = $e;
        }

        try {
            $this->handleNotifyExternalsReminder('glasgow');
        } catch (\Exception $e) {
            $this->exceptions[] = $e;
        }

        try {
            $this->handleNotifyExternalsReminder('uestc');
        } catch (\Exception $e) {
            $this->exceptions[] = $e;
        }

        try {
            $this->handleExternalModerationDeadline('glasgow');
        } catch (\Exception $e) {
            $this->exceptions[] = $e;
        }

        try {
            $this->handleExternalModerationDeadline('uestc');
        } catch (\Exception $e) {
            $this->exceptions[] = $e;
        }

        try {
            $this->handlePrintReadyDeadline('glasgow');
        } catch (\Exception $e) {
            $this->exceptions[] = $e;
        }

        try {
            $this->handlePrintReadyDeadline('uestc');
        } catch (\Exception $e) {
            $this->exceptions[] = $e;
        }

        if (count($this->exceptions) > 0) {
            $messages = collect($this->exceptions)->each(fn ($e) => $e->getMessage() . $e->getTraceAsString());
            throw new TimedNotificationException($messages);
        }
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

        $currentSemester = $this->getCurrentSemester();

        if (option('date_receive_call_for_papers_email_sent_semester_' . $currentSemester)) {
            return;
        }

        // TODO we need to break apart the date_receive_call_for_papers option above for Glasgow and UESTC
        $optionName = "glasgow_staff_submission_deadline";
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

        $emailAddresses = Course::with('setters')
            ->forSemester($currentSemester)
            ->get()
            ->flatMap(function ($course) {
                return $course->setters->pluck('email');
            })->filter()->unique();

        $emailAddresses->each(function ($email) use ($date) {
            Mail::to($email)->later(now()->addSeconds(rand(1, 200)), new CallForPapersMail($date));
        });

        option(['date_receive_call_for_papers_email_sent_semester_' . $currentSemester => now()->format('Y-m-d')]);
    }

    protected function getCurrentSemester(): int
    {
        $startSemesterOne = Carbon::createFromFormat('Y-m-d', option('start_semester_1'));
        $startSemesterTwo = Carbon::createFromFormat('Y-m-d', option('start_semester_2'));
        $startSemesterThree = Carbon::createFromFormat('Y-m-d', option('start_semester_3'));

        if (now()->between($startSemesterOne, $startSemesterTwo)) {
            return 1;
        }

        if (now()->between($startSemesterTwo, $startSemesterThree)) {
            return 2;
        }

        if (now()->gte($startSemesterThree)) {
            return 3;
        }

        throw new RuntimeException('Could not figure out semester');
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

        if ($date->clone()->subWeek()->dayOfYear == now()->dayOfYear) {
            $subType = 'upcoming';
        } else {
            $subType = 'reminder';
        }

        $currentSemester = $this->getCurrentSemester();

        if (option("{$optionName}_email_sent_{$subType}_semester_{$currentSemester}")) {
            return;
        }

        $emailAddresses = collect([]);
        if ($subType == 'upcoming') {
            $mailableName = SubmissionDeadlineMail::class;
            $emailAddresses = $this->getAllSetterEmails($area);
        } else {
            $mailableName = SubmissionDeadlinePassedMail::class;
            $emailAddresses = $this->getIncompletePaperworkSetterEmails($area);
        }

        $emailAddresses->each(function ($email) use ($mailableName, $date) {
            Mail::to($email)->later(now()->addSeconds(rand(1, 200)), new $mailableName($date));
        });

        option(["{$optionName}_email_sent_{$subType}_semester_{$currentSemester}" => now()->format('Y-m-d')]);
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

        if ($date->clone()->subDays(3)->dayOfYear == now()->dayOfYear) {
            $subType = 'upcoming';
        } else {
            $subType = 'reminder';
        }

        $currentSemester = $this->getCurrentSemester();
        if (option("{$optionName}_email_sent_{$subType}_semester_{$currentSemester}")) {
            return;
        }

        if ($subType == 'upcoming') {
            $mailableName = ModerationDeadlineMail::class;
            $emailAddresses = $this->getAllModeratorEmails($area);
        } else {
            $mailableName = ModerationDeadlinePassedMail::class;
            $emailAddresses = $this->getIncompletePaperworkModeratorEmails($area);
        }

        $emailAddresses->each(function ($email) use ($mailableName, $date, $subType) {
            Mail::to($email)->later(now()->addSeconds(rand(1, 200)), new $mailableName($date));
        });

        option(["{$optionName}_email_sent_{$subType}_semester_{$currentSemester}" => now()->format('Y-m-d')]);
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

        $courses = Course::forArea($area)->with('papers')->get()->filter(function ($course) {
            return $course->papers->contains(function ($paper) {
                return $paper->subcategory == Paper::PAPER_FOR_REGISTRY;
            });
        });

        if ($date->clone()->subDay()->dayOfYear == now()->dayOfYear) {
            $mailableName = PrintReadyDeadlineMail::class;
        } else {
            $mailableName = PrintReadyDeadlinePassedMail::class;
        }

        Mail::to(option("teaching_office_contact_{$area}"))
            ->later(now()->addSeconds(rand(1, 200)), new $mailableName($date, $courses));

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
        return Course::forArea($area)->forSemester($this->semester)->with('setters')->get()->flatMap(function ($course) {
            return $course->setters->pluck('email');
        })->filter()->unique();
    }

    protected function getIncompletePaperworkSetterEmails(string $area)
    {
        return Course::forArea($area)->forSemester($this->semester)->doesntHave('checklists')->with('setters')->get()->flatMap(function ($course) {
            return $course->setters->pluck('email');
        })->filter()->unique();
    }

    protected function getAllModeratorEmails(string $area)
    {
        return Course::forArea($area)
            ->forSemester($this->semester)
            ->with('moderators')
            ->get()
            ->flatMap(function ($course) {
                return $course->moderators->pluck('email');
            })
            ->filter()
            ->unique();
    }

    protected function getIncompletePaperworkModeratorEmails(string $area)
    {
        return Course::forArea($area)->forSemester($this->semester)->with('moderators')
            ->where(function ($query) {
                $query->where('moderator_approved_main', '!=', true)
                    ->orWhere('moderator_approved_resit', '!=', true);
            })
            ->get()
            ->flatMap(function ($course) {
                return $course->moderators->pluck('email');
            })->filter()->unique();
    }
}
