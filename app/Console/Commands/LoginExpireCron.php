<?php

namespace App\Console\Commands;

use App\Models\LoginActivity;
use Carbon\Carbon;
use Illuminate\Console\Command;

class LoginExpireCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loginexpire:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //\Log::info("Cron is working fine!");
        LoginActivity::where([
            ['last_activity_time', '<', Carbon::now()->subMinutes(16)],
        ])
            ->whereNull('logged_out_at')
            ->delete();
    }
}
