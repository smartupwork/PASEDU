<?php

namespace App\Console\Commands;

use App\ZohoHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportDeals extends Command
{
    const OFF_SET = 0;
    const LIMIT = 200;

    private $off_set = self::OFF_SET;
    private $limit = self::LIMIT;
    private $page = 1;

    private $data = [
        'insert' => [],
        'update' => [],
        'delete' => [],
    ];

    private $existing_schedules = [];

    private $partners = [];
    private $programs = [];
    private $programs_price = [];
    private $contacts = [];

    private $states_key_by_name = [];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'importDeals:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Schedules from ZOHO server. Cron should be run every hours.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->existing_schedules = DB::table('pas_schedule')->pluck('zoho_id', 'id')->toArray();
        //dd($this->existing_schedules);
        $this->partners = DB::table('pas_partner')
            ->where('partner_type', '=', 'Active')
            ->pluck('id', 'zoho_id')
            ->toArray();

        $this->states_key_by_name = DB::table('pas_state')
            ->select([DB::raw('LOWER(`state_name`) AS state_name'), 'iso2_code'])
            ->pluck('iso2_code', 'state_name')
            ->toArray();

        $programs = DB::table('pas_program')->get()->all();

        $this->programs = array_column($programs, 'id', 'zoho_id');
        $this->programs_price = array_column($programs, 'unite_price', 'zoho_id');

        $this->contacts = DB::table('pas_contact')
            ->pluck('id', 'zoho_id')
            ->toArray();

        $this->getSchedules();
        $this->info('Total records Inserted('.count($this->data['insert']).') and Updated('.count($this->data['update']).') and Deleted('.count($this->data['delete']).').');
    }

    private function getSchedules(){
        //$before_1_hour = Carbon::now('GMT-5')->subMinutes(70)->format('c');

        $zoho_response = ZohoHelper::getInstance()->fetch('Deals', ['Account_Name', 'Deal_Name', 'Amount', 'Stage', 'Start_Date', 'Email', 'Phone', 'Street', 'City', 'State', 'Zip', 'Country', 'Payment_Type', 'End_Date', 'Start_Date', 'Program', 'Contact_Name'], $this->page, $this->limit);
//dd($zoho_response);
        if($zoho_response['status'] == 'error'){
            $this->error($zoho_response['message']);
            die;
        }

        if(count($zoho_response['data']) > 0){

            foreach ($zoho_response['data']['data'] as $deal) {
                $state_iso2 = null;
                $state_name = trim(rtrim(trim($deal['State']),'.'));
                if(!empty($state_name)){
                    if(strlen($state_name) == 2){
                        $state_iso2 = $state_name;
                    }else if(isset($this->states_key_by_name[strtolower($state_name)])){
                        $state_iso2 =  $this->states_key_by_name[strtolower($state_name)];
                    }
                }

                $zoho_data = [
                    'zoho_id' => $deal['id'],
                    'partner_id' => (isset($deal['Account_Name']['id']) && isset($this->partners[$deal['Account_Name']['id']])) ? $this->partners[$deal['Account_Name']['id']]: null,
                    'partner_zoho_id' => isset($deal['Account_Name']['id']) ? $deal['Account_Name']['id']: null,
                    'deal_name' => !empty($deal['Deal_Name']) ? DB::raw('AES_ENCRYPT("'.addslashes($deal['Deal_Name']).'", "'.$_ENV['AES_ENCRYPT_KEY'].'")'):null,
                    'email' => !empty($deal['Email']) ? DB::raw('AES_ENCRYPT("'.$deal['Email'].'", "'.$_ENV['AES_ENCRYPT_KEY'].'")'):null,
                    'phone' => !empty($deal['Phone']) ? DB::raw('AES_ENCRYPT("'.$deal['Phone'].'", "'.$_ENV['AES_ENCRYPT_KEY'].'")'):null,
                    'stage' => $deal['Stage'],
                    'start_date' => $deal['Start_Date'],
                    'end_date' => $deal['End_Date'],
                    'payment_amount' => (isset($deal['Program']['id']) && isset($this->programs_price[$deal['Program']['id']])) ? $this->programs_price[$deal['Program']['id']] : null,
                    'amount' => $deal['Amount'],
                    'street' => $deal['Street'],
                    'city' => $deal['City'],
                    'state' => $state_iso2,
                    'zip' => $deal['Zip'],
                    'country' => $deal['Country'],
                    'payment_type' => addslashes($deal['Payment_Type']),
                    'program_id' => (isset($deal['Program']['id']) && isset($this->programs[$deal['Program']['id']])) ? $this->programs[$deal['Program']['id']] : null,
                    'program_zoho_id' => isset($deal['Program']['id']) ? $deal['Program']['id']:null,
                    'contact_zoho_id' => isset($deal['Contact_Name']['id']) ? $deal['Contact_Name']['id']:null,
                    'contact_id' => isset($deal['Contact_Name']['id']) && isset($this->contacts[$deal['Contact_Name']['id']]) ? $this->contacts[$deal['Contact_Name']['id']] :null,
                ];
//echo '<pre>';print_r($zoho_data);die;
                if(in_array($deal['id'], $this->existing_schedules)){
                    $zoho_data['updated_at'] = date('Y-m-d H:i:s');
                    $this->data['update'][] = $zoho_data;
                }else {
                    $zoho_data['created_at'] = date('Y-m-d H:i:s');
                    $this->data['insert'][] = $zoho_data;
                }
            }

            if ($zoho_response['data']['info']['more_records']) {
                $this->off_set = $this->off_set == 0 ? $this->limit : $this->off_set + $this->limit;
                $this->page += 1;
                $this->getSchedules();
                ///$this->info($partner['partner_name'].' find more.');
            }else{
                //dd($this->data);
                if(count($this->data['insert']) > 0){
                    foreach (array_chunk($this->data['insert'],1000) as $insert_schedules) {
                        Db::table('pas_schedule')->insert($insert_schedules);
                    }
                }
                if(count($this->data['update']) > 0){
                    foreach (array_chunk($this->data['update'],100) as $update_schedules) {
                        foreach ($update_schedules as $update_schedule) {
                            Db::table('pas_schedule')->where('zoho_id', '=', $update_schedule['zoho_id'])->update($update_schedule);
                        }

                    }
                }
            }
        }
    }
}
