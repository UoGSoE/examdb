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

    // TODO remove all the rest of this stuff

    public function runAllNotifcationChecks(Tenant $tenant)
    {
        tenancy()->initialize($tenant);

        $this->semester = $this->getCurrentSemester();

        // 😢
        try {
            $this->handleCallForPapers();
        } catch (\Exception $e) {
            $this->exceptions[] = $e;
        }

        try {
            $this->handleSubmissionDeadline();
        } catch (\Exception $e) {
            $this->exceptions[] = $e;
        }

        try {
            $this->handleModerationDeadline();
        } catch (\Exception $e) {
            $this->exceptions[] = $e;
        }

        try {
            $this->handleNotifyExternalsReminder();
        } catch (\Exception $e) {
            $this->exceptions[] = $e;
        }

        try {
            $this->handleExternalModerationDeadline();
        } catch (\Exception $e) {
            $this->exceptions[] = $e;
        }

        try {
            $this->handlePrintReadyDeadline();
        } catch (\Exception $e) {
            $this->exceptions[] = $e;
        }

        if (count($this->exceptions) > 0) {
            $messages = collect($this->exceptions)->map(fn ($e) => $e->getMessage() . $e->getTraceAsString());
            throw new TimedNotificationException($messages);
        }
    }


    protected function handleCallForPapers()
    {
        if (! option('date_receive_call_for_papers')) {
            $this->info('Skipping call for papers email as no date set');
            throw new \Exception('Skipping call for papers email as no date set');
        }

        try {
            $date = Carbon::parse(option('date_receive_call_for_papers'));
        } catch (Exception $e) {
            throw new \Exception('Could not parse date_receive_call_for_papers');
        }

        if ($date->dayOfYear > now()->dayOfYear) {
            return;
        }

        if (option('date_receive_call_for_papers_email_sent_semester_' . $this->semester)) {
            return;
        }

        $optionName = "staff_submission_deadline";
        if (! option($optionName)) {
            throw new \Exception('Skipping submission deadline email as no date set');
        }

        try {
            $date = Carbon::parse(option($optionName));
        } catch (Exception $e) {
            throw new \Exception("Could not parse $optionName");

            return;
        }

        $emailAddresses = Course::with('setters')
            ->forSemester($this->semester)
            ->get()
            ->flatMap(function ($course) {
                return $course->setters->pluck('email');
            })->filter()->unique();

        $emailAddresses->each(function ($email) use ($date) {
            Mail::to($email)->later(now()->addSeconds(rand(1, 200)), new CallForPapersMail($date));
        });

        option(['date_receive_call_for_papers_email_sent_semester_' . $this->semester => now()->format('Y-m-d')]);
    }

    protected function getCurrentSemester(): int
    {
        $startSemesterOne = Carbon::createFromFormat('Y-m-d', option('start_semester_1', now()->subYears(1)->format('Y-m-d')));
        $startSemesterTwo = Carbon::createFromFormat('Y-m-d', option('start_semester_2', now()->subYears(1)->format('Y-m-d')));
        $startSemesterThree = Carbon::createFromFormat('Y-m-d', option('start_semester_3', now()->subYears(1)->format('Y-m-d')));

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

    protected function handleSubmissionDeadline()
    {
        $optionName = "staff_submission_deadline";
        if (! option($optionName)) {
            throw new \Exception('Skipping submission deadline email as no date set');
        }

        try {
            $date = Carbon::parse(option($optionName));
        } catch (Exception $e) {
            throw new \Exception("Could not parse $optionName");
        }

        if ($date->dayOfYear != now()->subDay()->dayOfYear && $date->dayOfYear != now()->addWeek()->dayOfYear) {
            return;
        }

        if ($date->clone()->subWeek()->dayOfYear == now()->dayOfYear) {
            $subType = 'upcoming';
        } else {
            $subType = 'reminder';
        }

        if (option("{$optionName}_email_sent_{$subType}_semester_{$this->semester}")) {
            return;
        }

        $emailAddresses = collect([]);
        if ($subType == 'upcoming') {
            $emailAddresses = $this->getAllSetterEmails();
            $emailAddresses->each(function ($email) use ($date) {
                Mail::to($email)->later(now()->addSeconds(rand(1, 200)), new SubmissionDeadlineMail($date));
            });
        } else {
            $emailAddresses = $this->getIncompletePaperworkSetterEmails();
            collect($emailAddresses)->each(function ($courses, $email) use ($date) {
                Mail::to($email)->later(now()->addSeconds(rand(1, 200)), new SubmissionDeadlinePassedMail($date, $courses));
            });
        }

        option(["{$optionName}_email_sent_{$subType}_semester_{$this->semester}" => now()->format('Y-m-d')]);
    }

    protected function handleModerationDeadline()
    {
        $optionName = "internal_moderation_deadline";
        if (! option($optionName)) {
            throw new \Exception('Skipping moderation deadline email as no date set');
        }

        try {
            $date = Carbon::parse(option($optionName));
        } catch (Exception $e) {
            throw new \Exception("Could not parse $optionName");
        }

        if ($date->dayOfYear != now()->subDay()->dayOfYear && $date->dayOfYear != now()->addDays(3)->dayOfYear) {
            return;
        }

        if ($date->clone()->subDays(3)->dayOfYear == now()->dayOfYear) {
            $subType = 'upcoming';
        } else {
            $subType = 'reminder';
        }

        if (option("{$optionName}_email_sent_{$subType}_semester_{$this->semester}")) {
            return;
        }

        if ($subType == 'upcoming') {
            $mailableName = ModerationDeadlineMail::class;
            $emailAddresses = $this->getAllModeratorEmails();
            $emailAddresses->each(function ($email) use ($mailableName, $date) {
                Mail::to($email)->later(now()->addSeconds(rand(1, 200)), new $mailableName($date));
            });
        } else {
            $emailAddresses = $this->getIncompletePaperworkModeratorEmails();
            collect($emailAddresses)->each(function ($courses, $email) use ($date) {
                Mail::to($email)->later(now()->addSeconds(rand(1, 200)), new ModerationDeadlinePassedMail($date, $courses));
            });
        }


        option(["{$optionName}_email_sent_{$subType}_semester_{$this->semester}" => now()->format('Y-m-d')]);
    }

    protected function handleNotifyExternalsReminder()
    {
        $optionName = "date_remind_office_externals";
        if (! option($optionName)) {
            throw new \Exception('Skipping reminder about externals email as no date set');
        }

        try {
            $date = Carbon::parse(option($optionName));
        } catch (Exception $e) {
            throw new \Exception("Could not parse $optionName");
        }

        if ($date->dayOfYear != now()->dayOfYear) {
            return;
        }

        if (option("{$optionName}_email_sent")) {
            return;
        }

        Mail::to(option("teaching_office_contact"))
            ->later(now()->addSeconds(rand(1, 200)), new NotifyExternalsReminderMail);

        option(["{$optionName}_email_sent" => now()->format('Y-m-d')]);
    }

    protected function handlePrintReadyDeadline()
    {
        $optionName = "print_ready_deadline";
        if (! option($optionName)) {
            throw new \Exception('Skipping print ready deadline email as no date set');
        }

        try {
            $date = Carbon::parse(option($optionName));
        } catch (Exception $e) {
            throw new \Exception("Could not parse $optionName");
        }

        if ($date->dayOfYear != now()->subDay()->dayOfYear && $date->dayOfYear != now()->addDay()->dayOfYear) {
            return;
        }

        if (option("{$optionName}_email_sent")) {
            return;
        }

        $courses = Course::with('papers')->get()->filter(function ($course) {
            return ! $course->papers->contains(function ($paper) {
                return $paper->subcategory == Paper::PAPER_FOR_REGISTRY;
            });
        });

        if ($date->clone()->subDay()->dayOfYear == now()->dayOfYear) {
            $mailableName = PrintReadyDeadlineMail::class;
        } else {
            $mailableName = PrintReadyDeadlinePassedMail::class;
        }

        Mail::to(option("teaching_office_contact"))
            ->later(now()->addSeconds(rand(1, 200)), new $mailableName($date, $courses));

        if ($date->addDay()->dayOfYear == now()->dayOfYear) {
            option(["{$optionName}_email_sent" => now()->format('Y-m-d')]);
        }
    }

    protected function handleExternalModerationDeadline()
    {
        $optionName = "external_moderation_deadline";
        if (! option($optionName)) {
            throw new \Exception('Skipping reminder about external moderation email as no date set');
        }

        try {
            $date = Carbon::parse(option($optionName));
        } catch (Exception $e) {
            throw new \Exception("Could not parse $optionName");
        }

        if ($date->dayOfYear != now()->dayOfYear) {
            return;
        }

        if (option("{$optionName}_email_sent")) {
            return;
        }

        Mail::to(option("teaching_office_contact"))
            ->later(now()->addSeconds(rand(1, 200)), new ExternalModerationDeadlineMail);

        option(["{$optionName}_email_sent" => now()->format('Y-m-d')]);
    }

    protected function getAllSetterEmails()
    {
        return Course::forSemester($this->semester)->with('setters')->get()->flatMap(function ($course) {
            return $course->setters->pluck('email');
        })->filter()->unique();
    }

    /**
     *
     *  Structure of returned array :
     *  [
     *   $unique_email => [ $course_code, $course_code, $course_code],
     *   ...,
     *  ]
     *
     */
    protected function getIncompletePaperworkSetterEmails(): array
    {
        $result = [];

        $courses = Course::forSemester($this->semester)->doesntHave('checklists')->with('setters')->get();
        foreach ($courses as $course) {
            foreach ($course->setters as $setter) {
                $result[$setter->email][] = $course->code;
            }
        }

        return $result;
    }

    protected function getAllModeratorEmails()
    {
        return Course::forSemester($this->semester)
            ->with('moderators')
            ->get()
            ->flatMap(function ($course) {
                return $course->moderators->pluck('email');
            })
            ->filter()
            ->unique();
    }

    /**
     * Structure of returned array :
     * [
     *   'unique_email_address' => ['ENG1234', 'ENG5432', 'ENG9191],
     *   ...,
     * ]
     *
     */
    protected function getIncompletePaperworkModeratorEmails(): array
    {
        $courses = Course::forSemester($this->semester)->with('moderators')
            ->where(function ($query) {
                $query->where('moderator_approved_main', '!=', true)
                    ->orWhere('moderator_approved_resit', '!=', true);
            })
            ->get();

        $result = [];
        foreach ($courses as $course) {
            foreach ($course->moderators as $moderator) {
                $result[$moderator->email][] = $course->code;
            }
        }
        return $result;
    }
}
