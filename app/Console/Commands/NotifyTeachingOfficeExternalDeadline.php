<?php

namespace App\Console\Commands;

use App\Mail\NotifyTeachingOfficeExternalDeadline as Notification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotifyTeachingOfficeExternalDeadline extends Command
{
    protected $validAreas = ['glasgow', 'uestc'];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exampapers:notifyteachingofficeexternals {--area= : either glasgow or uestc}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify the teaching office that the external deadline is up';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $area = $this->option('area');

        if (! in_array($area, $this->validAreas)) {
            $this->error('Invalid area given : '.$area);

            return;
        }
        $dateOption = "date_remind_{$area}_office_externals";
        if (! option_exists($dateOption)) {
            abort(500, "No 'date_remind_{$area}_office_externals' option set");
        }

        $deadline = Carbon::createFromFormat('Y-m-d', option($dateOption));
        if ($deadline->gt(now())) {
            return;
        }

        $emailAddress = option("teaching_office_contact_{$area}");
        if (! $emailAddress) {
            abort(500, "No 'teaching_office_contact' set for area {$area}");
        }

        $lastNotifiedDate = option("date_remind_{$area}_office_externals_email_sent");
        if ($lastNotifiedDate) {
            return;
        }

        Mail::to($emailAddress)->queue(new Notification($area));

        option(["date_remind_{$area}_office_externals_email_sent" => now()->format('Y-m-d H:i')]);
    }
}
