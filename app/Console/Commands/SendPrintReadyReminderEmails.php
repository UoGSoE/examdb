<?php

namespace App\Console\Commands;

use App\Models\Paper;
use App\Models\Course;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\PrintReadyPaperReminderMail;

class SendPrintReadyReminderEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'examdb:send-print-ready-reminder-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder emails to staff about print ready papers that have not been actioned';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $courses = Course::has('latestPrintReadyPaper')
                    ->with(['latestPrintReadyPaper', 'setters'])
                    ->get()
                    ->filter(fn ($course) => is_null($course->latestPrintReadyPaper->getRawOriginal('print_ready_approved')))
                    ->filter(fn ($course) => $course->latestPrintReadyPaper->created_at->isBefore(now()->subHours(48)))
                    ->filter(fn ($course) => ! $course->latestPrintReadyPaper->print_ready_reminder_sent);

        $emailList = [];
        foreach ($courses as $course) {
            foreach ($course->setters as $setter) {
                $emailList[$setter->email][] = $course->code;
            }
        }
        foreach ($emailList as $email => $courseCodes) {
            if (count($courseCodes) == 0) {
                continue;
            }
            Mail::to($email)->later(now()->addSeconds(rand(1, 300)), new PrintReadyPaperReminderMail(collect($courseCodes)));
            $courses->whereIn('code', $courseCodes)->each(fn ($course) => $course->latestPrintReadyPaper->update(['print_ready_reminder_sent' => now()]));
        }

        return Command::SUCCESS;
    }
}
