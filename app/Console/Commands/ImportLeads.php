<?php

namespace App\Console\Commands;

use App\EmailHelper;
use App\EmailRequest;
use App\Models\EmailTemplates;
use App\Models\Leads;
use App\Models\Partner;
use App\Models\Program;
use App\Models\Timezone;
use App\Utility;
use App\ZohoHelper;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use PHPMailer\PHPMailer\PHPMailer;

class ImportLeads extends Command
{
    const OFF_SET = 0;
    const LIMIT = 200;

    private $off_set = self::OFF_SET;
    private $limit = self::LIMIT;
    private $page = 1;

    private $total = 1;

    private $data = [
        'insert' => [],
        'update' => [],
        'zoho' => [],
        'our_db_records' => [],
    ];

    private $countries = [];
    private $timezones = [];
    private $programs = [];
    private $partners = [];
    private $zoho_our_server = [];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'importLeads:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Partner schedules from ZOHO server.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $this->total = DB::table('pas_leads')->count('id');
        $this->countries = array_column(DB::table('pas_country')->get()->toArray(), 'id', 'country_name');
        $this->timezones = array_column(Timezone::select(['id', 'timezone'])->get()->toArray(),'id', 'timezone');
        $this->programs = array_column(Program::select(['id', 'zoho_id'])->get()->toArray(), 'id', 'zoho_id');
        $this->partners = array_column(Partner::select(['id', 'partner_name'])->get()->toArray(), 'id', 'partner_name');

        $this->getLeads();
        $this->info('Total records Inserted('.count($this->data['insert']).') and Updated('.count($this->data['update']).').');
    }

    private function getLeads(){
        try{
            //dump([$this->page, $this->limit]);
            $our_db_leads = DB::table('pas_leads AS l')
                ->select('l.name_of_requester', 'l.email_of_requester', 'l.email', 'l.firstname', 'l.lastname', 'l.partner_id', 'l.id', 'l.zoho_id', 'l.phone', 'pa.partner_name', 'name_of_requester', 'email_of_requester', 'l.address', 'l.city', 'l.zip', 'country_name', 'pr.name AS program_name', 'category_of_interest', 'financing_needs', 'inquiry_message', 'timezone')
                ->whereNotNull('l.zoho_id')
                ->join('pas_partner AS pa', 'pa.id', '=', 'l.partner_id')
                //->join('pas_users AS u', 'u.id', '=', 's.created_by')
                ->leftJoin('pas_program AS pr', 'pr.id', '=', 'l.interested_program')
                ->leftJoin('pas_timezone AS tz', 'tz.id', '=', 'l.time_zone')
                ->leftJoin('pas_country AS cnt', 'cnt.id', '=', 'l.country')
                //->leftJoin('pas_state AS sts', 'sts.id', '=', 'l.state')
                ->where('pa.partner_type', '=', 'Active')
                ->offset($this->off_set)
                ->limit($this->limit)
                ->get()
                ->toArray();
            //dump($our_db_leads);die;

            if($our_db_leads){
                //dump($our_db_leads);die;
                $ids = [];
                foreach ($our_db_leads as $our_db_lead) {
                    $zoho_key_id[$our_db_lead->zoho_id] = $our_db_lead;
                    $ids[] = $our_db_lead->zoho_id;
                }
                //dump($zoho_key_id);die;

                $zoho_leads = ZohoHelper::getInstance()->fetchByIds('Leads', $ids, ['School', 'First_Name', 'Last_Name', 'Email', 'Street', 'Phone', 'Country', 'Program', 'Financing_Needs', 'Category_of_Interest', 'Time_Zone', 'Inquiry_Message']);
                //dd($zoho_leads);

                if(isset($zoho_leads['data'])){
                    foreach ($zoho_leads['data'] as $zoho_lead) {
                        if(isset($zoho_key_id[$zoho_lead['id']])){
                            $zoho_our_server = $zoho_key_id[$zoho_lead['id']];
                            if($zoho_lead['School']['name'] != $zoho_our_server->partner_name ||
                                $zoho_lead['First_Name'] != $zoho_our_server->firstname ||
                                $zoho_lead['Last_Name'] != $zoho_our_server->lastname ||
                                $zoho_lead['Email'] != $zoho_our_server->email ||
                                ($zoho_lead['Phone'] != null && $zoho_lead['Phone'] != $zoho_our_server->phone) ||
                                ($zoho_lead['Street'] != null && $zoho_lead['Street'] != $zoho_our_server->address) ||
                                ($zoho_lead['Country'] != null && $zoho_lead['Country'] != $zoho_our_server->country_name) ||
                                (isset($zoho_lead['Program']['name']) && $zoho_lead['Program']['name'] != $zoho_our_server->program_name) ||
                                ($zoho_lead['Financing_Needs'] != null && $zoho_lead['Financing_Needs'] != $zoho_our_server->financing_needs) ||
                                ($zoho_lead['Category_of_Interest'] != null && $zoho_lead['Category_of_Interest'] != $zoho_our_server->category_of_interest) ||
                                ($zoho_lead['Time_Zone'] != null && $zoho_lead['Time_Zone'] != $zoho_our_server->timezone) ||
                                ($zoho_lead['Inquiry_Message'] != null && $zoho_lead['Inquiry_Message'] != $zoho_our_server->inquiry_message)
                            ){
                                //dump($zoho_lead);
                                $this->data['our_db_records'][] = $zoho_our_server;
                                $this->data['zoho'][] = $zoho_lead;
                                $this->data['update'][] = [
                                    'id' => $zoho_our_server->id,
                                    'partner_id' => isset($this->partners[$zoho_lead['School']['name']]) ? $this->partners[$zoho_lead['School']['name']]: null,
                                    'firstname' => addslashes($zoho_lead['First_Name']),
                                    'lastname' => addslashes($zoho_lead['Last_Name']),
                                    'email' => $zoho_lead['Email'],
                                    'phone' => $zoho_lead['Phone'],
                                    'address' => addslashes($zoho_lead['Street']),
                                    'country' => isset($this->countries[$zoho_lead['Country']]) ? $this->countries[$zoho_lead['Country']]:null,
                                    'interested_program' => (isset($zoho_lead['Program']['id']) && isset($this->programs[$zoho_lead['Program']['id']]) ) ? $this->programs[$zoho_lead['Program']['id']]: null,
                                    'financing_needs' => addslashes($zoho_lead['Financing_Needs']),
                                    'category_of_interest' => addslashes($zoho_lead['Category_of_Interest']),
                                    'inquiry_message' => addslashes($zoho_lead['Inquiry_Message']),
                                    'time_zone' => isset($this->timezones[$zoho_lead['Time_Zone']]) ? $this->timezones[$zoho_lead['Time_Zone']]: null,
                                    //'created_at' => date('Y-m-d H:i:s'),
                                ];
                            }
                        }
                    }
                }

                //dump([$this->total, $this->off_set, $this->limit]);
                if($this->total > ($this->page * $this->limit)){
                    $this->off_set = $this->off_set == 0 ? $this->limit : $this->off_set + $this->limit;
                    $this->page += 1;
                    $this->getLeads();
                }else{
                    //dd($this->data);
                    foreach ($this->data['update'] as $key => $lead) {
                        $lid = $lead['id'];
                        unset($lead['id']);
                        Leads::where('id', '=', $lid)->update($lead);
                        $this->sendStudentEmail($this->data['our_db_records'][$key], $lead, $this->data['zoho'][$key]);
                    }

                    if(isset($this->data['update']) && count($this->data['update']) > 0){
                        $leeds_data['action'] = 'update';
                        $leeds_data['action_via'] = 'cron';
                        $leeds_data['url'] = 'cron-leads';
                        $leeds_data['ip_address'] = Utility::getClientIp();
                        $leeds_data['session_id'] = Session::getId();
                        $leeds_data['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
                        $leeds_data['old_data'] = json_encode($this->data['our_db_records']);
                        $leeds_data['new_data'] = json_encode($this->data['update']);
                        $leeds_data['ref_ids'] = implode(',', array_column($this->data['update'], 'id'));
                        DB::table('pas_user_activity')->insert($leeds_data);
                    }

                }

            }else{
                $this->info('Data not found');
            }
        }catch (\Exception $e){
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
    }

    private function sendStudentEmail($lead, $data_to_update, $zoho_lead){
        $placeholder['PARTNER'] = $zoho_lead['School']['name'];
        $placeholder['NAME_OF_REQUESTER'] = $lead->name_of_requester;
        $placeholder['EMAIL_OF_REQUESTER'] = $lead->email_of_requester;
        $placeholder['FIRSTNAME'] = $data_to_update['firstname'];
        $placeholder['LASTNAME'] = $data_to_update['lastname'];
        $placeholder['PHONE'] = $data_to_update['phone'];
        $placeholder['EMAIL'] = $data_to_update['email'];
        $placeholder['ADDRESS'] = $data_to_update['address'];
        $placeholder['COUNTRY'] = $zoho_lead['Country'];
        $placeholder['PROGRAM'] = isset($zoho_lead['Program']['name']) ? $zoho_lead['Program']['name']:'';
        $placeholder['FINANCING_NEEDS'] = $data_to_update['financing_needs'];
        $placeholder['CATEGORY_OF_INTEREST'] = $data_to_update['category_of_interest'];
        $placeholder['INQUIRY_MESSAGE'] = $data_to_update['inquiry_message'];
        $placeholder['TIMEZONE'] = $zoho_lead['Time_Zone'];

        $email_req = new EmailRequest();
        $email_req->setTemplate(EmailTemplates::ZOHO_LEADS_DETAIL_UPDATED_FOR_PARTNER)
            ->setPlaceholder($placeholder)
            /*->setFromName($_ENV['FROM_NAME'])
            ->setFromEmail($_ENV['FROM_EMAIL'])*/
            ->setTo([[$lead->email_of_requester, $lead->name_of_requester]])
//            ->setTo([['rajneesh@xoomwebdevelopment.com', 'Rajneesh']])
            ->setLogSave(true);

        $email_helper = new EmailHelper($email_req);
        $email_helper->sendEmail();
    }
}
