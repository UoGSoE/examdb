<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('activitylog:clean')->daily();
        $schedule->command('exampapers:notify-paperwork-incomplete --area=glasgow')->dailyAt('02:00');
        $schedule->command('exampapers:notify-paperwork-incomplete --area=uestc')->dailyAt('02:30');
        $schedule->command('exampapers:notifyteachingofficeexternals --area=glasgow')->dailyAt('03:30');
        $schedule->command('exampapers:notifyteachingofficeexternals --area=uestc')->dailyAt('03:40');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
