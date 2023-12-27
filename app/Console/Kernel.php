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
        Commands\LoginExpireCron::class,
        Commands\ImportProgram::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('loginexpire:cron');

        //$schedule->command('importPartner:cron');

        //$schedule->command('importProgram:cron');

        $schedule->command('importPriceBook:cron');

        $schedule->command('importPriceBookProgramMap:cron');

        $schedule->command('importPriceBookPartnerMap:cron');

        $schedule->command('importSchedule:cron');

        $schedule->command('importLeads:cron'); //$_ENV['ADMIN_EMAIL_SECOND']

        //$schedule->command('importPartnerSellingCount:cron');

        $schedule->command('importDashboardReport:cron');

        $schedule->command('updateZohoNotification:cron');

        //$schedule->command('importEnrollment:cron');

        $schedule->command('partnerUpdate:hook');

        $schedule->command('programUpdate:hook');

        $schedule->command('enrollmentUpdate:hook');
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
