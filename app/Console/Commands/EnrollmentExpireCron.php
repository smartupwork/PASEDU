<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EnrollmentExpireCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enrollmentExpire:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update enrollment Expire or Not.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $response = DB::table('pas_enrollment')
            ->join('pas_program', 'pas_program.zoho_id', '=', 'pas_enrollment.program_zoho_id')
            ->join('pas_partner', 'pas_partner.id', '=', 'pas_enrollment.partner_id')
            ->where('end_date', '<', date('Y-m-d'))
            ->where('program_type', '!=', 'Career Training Program')
            ->where('partner_name', 'NOT LIKE', '%Cinépolis%')
            ->whereIn('pas_enrollment.status', ['Active', 'Extended'])
            //->get()->all();
            ->update(['pas_enrollment.status' => 'Expired', 'pas_enrollment.updated_at' => date('Y-m-d H:i:s')]);

        $this->info("Total ".$response." enrollments updated. ");
    }
}
