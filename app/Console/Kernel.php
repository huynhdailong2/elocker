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
//        $schedule->command('database:snapshot')->hourly();
        $schedule->command('physical-schedule:run cycle_count')->everyMinute();
        $schedule->command('physical-schedule:run inventory_count')->everyMinute();
        $schedule->command('physical-schedule:run alert_weighing_system')->everyMinute();
        $schedule->command('physical-schedule:run maintenance')->everyMinute();
        $schedule->command('unlock-bin-processing:run')->everyMinute();
//        $schedule->command('weighing-system:notification')->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
