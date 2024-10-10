<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

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
     * the job should execute on 15th of every month at 23:59
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // generate monthly teacher payroll
        $schedule->command('payroll:generate')->monthlyOn(15, '23:59');

        // update classes attendance status
        $schedule->command('command:TrialClassesStatusUpdateAsPerAttendance')->everyMinute();
        $schedule->command('command:WeeklyClassesStatusUpdateAsPerAttendance')->everyMinute();
    }
}
