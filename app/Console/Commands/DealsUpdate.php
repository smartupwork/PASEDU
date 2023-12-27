<?php

namespace App\Console\Commands;

use App\EmailHelper;
use App\EmailRequest;
use App\Utility;
use App\ZohoHelper;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class DealsUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dealsUpdate:hook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedule(Deals) Update with ZOHO Notification API';

    private $hook_table = 'pas_zoho_notification';

    private $module_name = 'Deals';

    private $table = 'pas_schedule';

    private $log_data = ['insert' => [], 'update' => []];

    private $module_fields = [
        'Account_Name', 'Deal_Name', 'Amount', 'Stage', 'Start_Date', 'Email', 'Phone', 'Street', 'City', 'State', 'Zip', 'Country', 'Payment_Type', 'End_Date', 'Start_Date', 'Program'
    ];


    private $data = [
        'insert' => [],
        'update' => [],
        'delete' => [],
    ];

    private $partners = [];
    private $programs = [];

    private $states_key_by_name = [];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /*$hooks = DB::table($this->hook_table)->where([
            ['module', '=', $this->module_name],
            ['is_executed', '=', 0],
        ])
            ->limit(200)
            ->get()
            ->toArray();

        if(count($hooks) > 0) {
            try{
                $this->states_key_by_name = DB::table('pas_state')
                    ->select([DB::raw('LOWER(`state_name`) AS state_name'), 'iso2_code'])
                    ->pluck('iso2_code', 'state_name')
                    ->toArray();

                $this->partners = DB::table('pas_partner')
                    ->where('partner_type', '=', 'Active')
                    ->pluck('id', 'zoho_id')
                    ->toArray();

                $this->programs = DB::table('pas_program')
                    ->pluck('id', 'zoho_id')
                    ->toArray();

                $hook_action = [];
                //dd($hooks);
                $hooks_to_be_delete = array_column($hooks, 'id');
                //dd($hooks_to_be_delete);
                $hook_action['all'] = [];
                $hook_action['insert'] = [];
                $hook_action['update'] = [];
                $hook_action['delete'] = [];
                foreach ($hooks as $hook) {
                    if ($hook->operation != 'delete') {
                        $hook_action['all'][] = $hook->ids;
                    }
                    $hook_action[$hook->operation][] = $hook->ids;
                }
                //dd($hook_action);

                $zoho_records = ZohoHelper::getInstance()->fetchByIds($this->module_name, array_unique($hook_action['all']), $this->module_fields);
//dd($zoho_records);

                if (isset($zoho_records['data']) && count($zoho_records['data']) > 0) {
                    foreach ($zoho_records['data'] as $deal) {
                        $state_iso2 = null;
                        $state_name = trim(rtrim(trim($deal['State']), '.'));
                        if (!empty($state_name)) {
                            if (strlen($state_name) == 2) {
                                $state_iso2 = $state_name;
                            } else if (isset($this->states_key_by_name[strtolower($state_name)])) {
                                $state_iso2 = $this->states_key_by_name[strtolower($state_name)];
                            }
                        }

                        $data = [
                            'zoho_id' => $deal['id'],
                            'partner_id' => (isset($deal['Account_Name']['id']) && isset($this->partners[$deal['Account_Name']['id']])) ? $this->partners[$deal['Account_Name']['id']] : null,
                            'partner_zoho_id' => isset($deal['Account_Name']['id']) ? $deal['Account_Name']['id'] : null,
                            'deal_name' => $deal['Deal_Name'],
                            'email' => $deal['Email'],
                            'phone' => $deal['Phone'],
                            'street' => $deal['Street'],
                            'city' => $deal['City'],
                            'state' => $state_iso2,
                            'zip' => $deal['Zip'],
                            'country' => $deal['Country'],
                            'stage' => $deal['Stage'],
                            'start_date' => $deal['Start_Date'],
                            'end_date' => $deal['End_Date'],
                            'amount' => $deal['Amount'],
                            'payment_type' => $deal['Payment_Type'],
                            'program_id' => (isset($deal['Program']['id']) && isset($this->programs[$deal['Program']['id']])) ? $this->programs[$deal['Program']['id']] : null,
                            'program_zoho_id' => isset($deal['Program']['id']) ? $deal['Program']['id']:null,

                        ];

                        if (count($hook_action['insert']) > 0 && in_array($deal['id'], $hook_action['insert'])) {
                            $data['created_at'] = date('Y-m-d H:i:s');
                            $this->data['insert'][] = $data;
                            $this->log_data['insert'][] = $data;
                            $this->log_data['insert_ids'][] = $deal['id'];
                        } else if (count($hook_action['update']) > 0 && in_array($deal['id'], $hook_action['update'])) {
                            $data['updated_at'] = date('Y-m-d H:i:s');
                            $this->data['update'][] = $data;
                            $this->log_data['update'][] = $data;
                            $this->log_data['update_ids'][] = $deal['id'];
                        }
                    }
                } else {
                    $this->warn('There are not update or insert deals.');
                }

                //dd($this->data);

                if (count($this->data['insert']) > 0) {
                    DB::table($this->table)->insert($this->data['insert']);
                }

                if (count($this->data['update']) > 0) {
                    foreach ($this->data['update'] as $hook_update) {
                        DB::table($this->table)
                            ->where('zoho_id', '=', $hook_update['zoho_id'])
                            ->update($hook_update);
                    }
                }

                if (count($hook_action['delete']) > 0) {
                    DB::table($this->table)->whereIn('zoho_id', $hook_action['delete'])->delete();
                }

                if (count($hooks_to_be_delete) > 0) {
                    DB::table($this->hook_table)->whereIn("id", $hooks_to_be_delete)->delete();
                }

                $leeds_data['action_via'] = 'cron';
                $leeds_data['url'] = 'cron-deals';
                $leeds_data['ip_address'] = Utility::getClientIp();
                $leeds_data['session_id'] = Session::getId();
                $leeds_data['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
                if(isset($this->log_data['update']) && count($this->log_data['update']) > 0){
                    $leeds_data['action'] = 'update';
                    //$leeds_data['old_data'] = json_encode($old_data);
                    $leeds_data['new_data'] = json_encode($this->log_data['update']);
                    $leeds_data['ref_ids'] = implode(',', $this->log_data['update_ids']);
                    DB::table('pas_user_activity')->insert($leeds_data);
                }
                if(isset($this->log_data['insert']) && count($this->log_data['insert']) > 0){
                    $leeds_data['action'] = 'create';
                    //$leeds_data['old_data'] = json_encode($old_data);
                    $leeds_data['new_data'] = json_encode($this->log_data['insert']);
                    $leeds_data['ref_ids'] = implode(',', $this->log_data['insert_ids']);
                    //dump($leeds_data);
                    DB::table('pas_user_activity')->insert($leeds_data);
                }

                $this->info('Total records Inserted(' . count($this->data['insert']) . ') and Updated(' . count($this->data['update']) . ' and Deleted(' . count($hook_action['delete']) . ').');
            }
            catch (\Exception $e){
                //dd($e->getMessage());
                $email_req = new EmailRequest();
                $email_req
                    ->setTo([
                        [$_ENV['DEVELOPER_EMAIL_FIRST'], "Xoom Web Development"],
                        //[$_ENV['DEVELOPER_EMAIL_SECOND'], "Info Xoom Web Development"],
                    ])
                    ->setSubject($_ENV['APP_ENV'].' PAS ERROR :: '.__CLASS__)
                    ->setBody('Line No. '.$e->getLine().' MSG. '.$e->getMessage())
                    ->setLogSave(false);

                $email_helper = new EmailHelper($email_req);
                $email_helper->sendEmail();
            }
        }else{
            $this->info('Data not found to update/insert/delete into notification API.');
        }*/

    }
}
