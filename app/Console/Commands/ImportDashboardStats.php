<?php

namespace App\Console\Commands;

use App\Models\Partner;
use App\ZohoHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportDashboardStats extends Command
{
    private $current_year_enrollments = 0;
    private $retention_rate = 0;

    private $lifetime_revenue = 0;
    private $current_year_revenue = 0;

    const STATUS_ACTIVE = 'Active';
    const STATUS_EXPIRED = 'Expired';
    const STATUS_EXTENDED = 'Extended';
    const STATUS_COMPLETED = 'Completed';
    const STATUS_COMPLETED_PENDING_PAYOFF = 'Completed- Pending Payoff';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'importDashboardStats:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Dashboard Report data from ZOHO server.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $partners = Db::table('pas_partner')
            ->where('partner_type', '=', 'Active')
            ->whereNotNull('zoho_id')
            ->get()
            ->toArray();

        foreach ($partners as $partner) {
                $this->updateEnrollmentData($partner); // DONE

                $this->updateActiveEnrollments($partner); // DONE

                $this->updateLifeTimeEnrollments($partner); // DONE

                $this->updateLifetimeRevenue($partner); // DONE
        }

        $this->info('ALl Partner dashboard record updated successfully.');
    }

    private function updateEnrollmentData($partner){
        try{
            $last_12_months = array();
            $now = date('Y-m');
            for($x = 12; $x >= 1; $x--) {
                $ym = date('Y-m', strtotime($now . " -$x month"));
                $last_12_months[$ym] = $ym;
            }
            //dd($last_12_months);

            $enrollmentData = DB::table('pas_enrollment')
                ->select(['partner_zoho_id', 'grand_total', 'status', 'start_date'])
                ->where('partner_id', '=', $partner->id)
                ->whereNotNull('status')
                ->get()
                ->all();

            //dd($enrollmentData);
            $current_year_enrollments = 0;
            $completion_rate_status_count = 0;
            $completion_rate_total_count = 0;
            $retention_rate_status_complated = 0;

            if(count($enrollmentData) > 0) {
                foreach ($enrollmentData as $enrollment) {
                    if(date('Y', strtotime($enrollment->start_date)) == date('Y') && in_array($enrollment->status, self::getReportStatus())){
                        $current_year_enrollments += 1;
                        //dump($current_year_enrollments);
                    }
                    if($enrollment->status == self::STATUS_COMPLETED || $enrollment->status == self::STATUS_COMPLETED_PENDING_PAYOFF){
                        $completion_rate_status_count += 1;
                    }
                    $completion_rate_total_count += 1;

                    if(in_array(date('Y-m', strtotime($enrollment->start_date)), $last_12_months) && in_array($enrollment->status, self::getReportStatus())){
                        $retention_rate_status_complated += 1;

                    }
                }

                $dashboard_partner = DB::table('pas_dashboard_report')->select(['current_year_enrollments', 'remaining_po_amount', 'completion_rate', 'conversion_rate', 'retention_rate', 'lifetime_revenue'])->where('partner_id', '=', $partner->id)->get()->first();

                $retention_rate = round(($retention_rate_status_complated / $completion_rate_total_count), 2);
                //dump($retention_rate);

                $completion_rate = round(($completion_rate_status_count / $completion_rate_total_count), 2);
                //dump($completion_rate);
                if ($dashboard_partner) {
                    if ($dashboard_partner->current_year_enrollments != $current_year_enrollments || $dashboard_partner->completion_rate != $completion_rate || $dashboard_partner->retention_rate != $retention_rate) {
                        DB::table('pas_dashboard_report')->where('partner_id', '=', $partner->id)->update([
                            'current_year_enrollments' => $current_year_enrollments,
                            'completion_rate' => $completion_rate,
                            'retention_rate' => $retention_rate,
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    }

                } elseif(!$dashboard_partner) {
                    $data['partner_id'] = $partner->id;
                    $data['current_year_enrollments'] = $current_year_enrollments;
                    $data['completion_rate'] = $completion_rate;
                    $data['retention_rate'] = $retention_rate;
                    $data['created_at'] = date('Y-m-d H:i:s');
                    DB::table('pas_dashboard_report')->insert($data);
                }
            }else{
                dump($partner->partner_name. ' has no data.');
            }
            $this->info($partner->partner_name.' dashboard data updated successfully.');
        }catch (\Exception $e){
            $this->error($e->getMessage());
        }

    }

    private function updateActiveEnrollments($partner){
        try{
            $active_enrolls = DB::table('pas_enrollment')
                ->where('partner_id', '=', $partner->id)
                ->where('status', '=', 'Active')
                ->count('id');

                $dashboard_partner = DB::table('pas_dashboard_report')->select(['active_enrollments'])->where('partner_id', '=', $partner->id)->get()->first();

                if ($dashboard_partner && $dashboard_partner->active_enrollments != $active_enrolls) {
                    DB::table('pas_dashboard_report')
                        ->where('partner_id', '=', $partner->id)
                        ->update(['active_enrollments' => $active_enrolls, 'updated_at' => date('Y-m-d H:i:s') ]);
                } elseif(!$dashboard_partner) {
                    $data['partner_id'] = $partner->id;
                    $data['active_enrollments'] = $active_enrolls;
                    $data['created_at'] = date('Y-m-d H:i:s');
                    DB::table('pas_dashboard_report')->insert($data);
                }
                $this->info($partner->partner_name.' dashboard Active Enrollments('.$active_enrolls.') updated successfully.');
        }catch (\Exception $e){
            $this->error($e->getMessage());
        }
    }

    /**
     * @param $partner
     */
    private function updateLifeTimeEnrollments($partner): void
    {
        try{
            /*$life_time_enrolls = ZohoHelper::getInstance()->fetchWithCount('Sales_Orders', "(Account_Name.id:equals:" . $partner->zoho_id . ")and((Status:equals:Active)or(Status:equals:Expired)or(Status:equals:Extended)or(Status:equals:Completed)or(Status:equals:".urlencode('Completed- Pending Payoff')."))");*/

            $life_time_enrolls = DB::table('pas_enrollment')
                ->select(['partner_zoho_id', 'grand_total', 'status', 'start_date'])
                ->where('partner_id', '=', $partner->id)
                ->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_EXPIRED, self::STATUS_EXTENDED, self::STATUS_COMPLETED, self::STATUS_COMPLETED_PENDING_PAYOFF])
                ->count('id');


            if (isset($life_time_enrolls)) {
                $dashboard_partner = DB::table('pas_dashboard_report')->select(['life_time_enrollments'])->where('partner_id', '=', $partner->id)->get()->first();

                if ($dashboard_partner && $dashboard_partner->life_time_enrollments != $life_time_enrolls) {
                    DB::table('pas_dashboard_report')->where('partner_id', '=', $partner->id)->update([
                        'life_time_enrollments' => $life_time_enrolls,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                } elseif(!$dashboard_partner) {
                    $data['partner_id'] = $partner->id;
                    $data['life_time_enrollments'] = $life_time_enrolls;
                    $data['created_at'] = date('Y-m-d H:i:s');
                    DB::table('pas_dashboard_report')->insert($data);
                }
                $this->info($partner->partner_name.' dashboard Life Time Enrollments('.$life_time_enrolls.') updated successfully.');
            }
        }catch (\Exception $e){
            $this->error($e->getMessage());
        }

    }

    private function updateLifetimeRevenue($partner){
        try{
            /*$lifetimeRevenue = ZohoHelper::getInstance()
                ->setTable('Deals')
                ->setColumns(['Deal_Name', 'Amount', 'Stage', 'Start_Date'])
                ->setWhere("(Account_Name = ".$partner->zoho_id.") AND ( ((Stage = 'Completed') OR (Stage = 'Enrollment Processed')) )")
                ->getAll();*/

            $lifetimeRevenue = DB::table('pas_schedule')
                ->select(['partner_zoho_id', 'amount', 'stage', 'start_date'])
                ->where('partner_id', '=', $partner->id)
                ->whereIn('stage', ['Completed', 'Enrollment Processed'])
                ->get()
                ->all();

            //dd($lifetimeRevenue);

            $lifetime_revenue = 0;
            $current_year_revenue = 0;
            if(count($lifetimeRevenue) > 0) {
                foreach ($lifetimeRevenue as $enrollment) {
                    $lifetime_revenue += $enrollment->amount;
                    if(date('Y', strtotime($enrollment->start_date)) == date('Y')){
                        $current_year_revenue += $enrollment->amount;
                    }
                }

                $dashboard_partner = DB::table('pas_dashboard_report')->select(['lifetime_revenue', 'current_year_revenue'])->where('partner_id', '=', $partner->id)->get()->first();

                if ($dashboard_partner) {
                    if ($dashboard_partner->lifetime_revenue != $lifetime_revenue || $dashboard_partner->current_year_revenue != $current_year_revenue) {
                        DB::table('pas_dashboard_report')->where('partner_id', '=', $partner->id)->update([
                            'lifetime_revenue' => $lifetime_revenue,
                            'current_year_revenue' => $current_year_revenue,
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    }

                } elseif(!$dashboard_partner) {
                    $data['partner_id'] = $partner->id;
                    $data['lifetime_revenue'] = $lifetime_revenue;
                    $data['current_year_revenue'] = $current_year_revenue;
                    $data['created_at'] = date('Y-m-d H:i:s');
                    DB::table('pas_dashboard_report')->insert($data);
                }

            }
            $this->info($partner->partner_name.' dashboard Life Time Revenue('.$lifetime_revenue.') AND Current Year Revenue('.$current_year_revenue.') updated successfully.');
        }catch (\Exception $e){
            $this->error($e->getMessage());
        }
    }

    public function getReportStatus(){
        return [
            self::STATUS_ACTIVE,
            self::STATUS_EXPIRED,
            self::STATUS_EXTENDED,
            self::STATUS_COMPLETED,
            self::STATUS_COMPLETED_PENDING_PAYOFF,
        ];
    }
}
