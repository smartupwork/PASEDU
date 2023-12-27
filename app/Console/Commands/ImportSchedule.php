<?php

namespace App\Console\Commands;

use App\EmailHelper;
use App\EmailRequest;
use App\Models\EmailTemplates;
use App\Models\Student;
use App\Utility;
use App\ZohoHelper;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ImportSchedule extends Command
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
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'importSchedule:cron';

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
    public function handle() {
        $this->total = DB::table('pas_student')->count('id');
        $this->getSchedules();
        $this->info('Total records Inserted('.count($this->data['insert']).') and Updated('.count($this->data['update']).').');
    }

    private function getSchedules(){
        try{
            $our_db_students = DB::table('pas_student AS s')
                ->select('p.email AS partner_email', DB::raw('p.partner_name AS partner_name'), DB::raw('AES_DECRYPT(s.first_name, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS first_name'), DB::raw('AES_DECRYPT(s.last_name, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS last_name'), 's.partner_id', 's.id', 's.zoho_id', DB::raw('AES_DECRYPT(s.email, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS email'), DB::raw('CONCAT(AES_DECRYPT(s.first_name, "'.$_ENV['AES_ENCRYPT_KEY'].'"), " ", AES_DECRYPT(s.last_name, "'.$_ENV['AES_ENCRYPT_KEY'].'")) AS student_name'), DB::raw('AES_DECRYPT(s.phone, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS phone'))
                ->join('pas_partner AS p', 'p.id', '=', 's.partner_id')
                ->where('p.partner_type', '=', 'Active')
                ->whereNotNull('s.zoho_id')
                ->offset($this->off_set)
                ->limit($this->limit)
                ->get()
                ->toArray();
            //dump($our_db_students);

            if($our_db_students){
                //dump($our_db_students);die;
                $ids = [];
                $zoho_key_id = [];
                foreach ($our_db_students as $our_db_student) {
                    $zoho_key_id[$our_db_student->zoho_id] = $our_db_student;
                    $ids[] = $our_db_student->zoho_id;
                }

                $zoho_leads = ZohoHelper::getInstance()->fetchByIds('Leads', $ids, ['First_Name', 'Last_Name', 'Email', 'Phone']);

                if(!isset($zoho_leads['data']) && isset($zoho_leads['status']) && $zoho_leads['status'] == 'error'){
                    $this->error($zoho_leads['message']);
                }else {
                    //dump($zoho_leads);
                    if(isset($zoho_leads['data'])) {
                        foreach ($zoho_leads['data'] as $zoho_lead) {
                            if (isset($zoho_key_id[$zoho_lead['id']])) {
                                $zoho_enrol = $zoho_key_id[$zoho_lead['id']];
                                if ($zoho_lead['First_Name'] != $zoho_enrol->first_name || $zoho_lead['Last_Name'] != $zoho_enrol->last_name || $zoho_lead['Email'] != $zoho_enrol->email || $zoho_lead['Phone'] != $zoho_enrol->phone) {

                                    $this->data['our_db_records'][] = $zoho_enrol;
                                    $this->data['update'][] = [
                                        'id' => $zoho_enrol->id,
                                        'first_name' => addslashes($zoho_lead['First_Name']),
                                        'last_name' => addslashes($zoho_lead['Last_Name']),
                                        'email' => $zoho_lead['Email'],
                                        'phone' => $zoho_lead['Phone']
                                    ];
                                }
                            } else {
                                $this->info('User is not exists into our database. ZOHO Leads ID:' . $zoho_lead['id']);
                            }
                        }
                    }

                    //dump([$this->total, $this->off_set, $this->limit]);
                    if($this->total > ($this->page * $this->limit)){
                        $this->off_set = $this->off_set == 0 ? $this->limit : $this->off_set + $this->limit;
                        $this->page += 1;
                        //dump([$this->total, $this->off_set, $this->limit]);
                        $this->getSchedules();
                    }else{
                        //dd($this->data);
                        foreach ($this->data['update'] as $key => $lead) {
                            $lead_id = $lead['id'];
                            unset($lead['id']);
                            $data_to_update = [
                                'first_name' => DB::raw('AES_ENCRYPT("'.addslashes($lead['first_name']).'", "'.$_ENV['AES_ENCRYPT_KEY'].'")'),
                                'last_name' => DB::raw('AES_ENCRYPT("'.addslashes($lead['last_name']).'", "'.$_ENV['AES_ENCRYPT_KEY'].'")'),
                                'email' => DB::raw('AES_ENCRYPT("'.$lead['email'].'", "'.$_ENV['AES_ENCRYPT_KEY'].'")'),
                                'phone' => DB::raw('AES_ENCRYPT("'.$lead['phone'].'", "'.$_ENV['AES_ENCRYPT_KEY'].'")'),
                                'updated_at' => date('Y-m-d H:i:s'),
                            ];

                            Student::where('id', '=', $lead_id)->update($data_to_update);
                            $this->sendStudentEmail($this->data['our_db_records'][$key], $lead);
                        }

                        if(isset($this->data['update']) && count($this->data['update']) > 0){
                            $leeds_data['action'] = 'update';
                            $leeds_data['action_via'] = 'cron';
                            $leeds_data['url'] = 'cron-schedule';
                            $leeds_data['ip_address'] = Utility::getClientIp();
                            $leeds_data['session_id'] = Session::getId();
                            $leeds_data['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
                            //$leeds_data['old_data'] = json_encode($old_data);
                            $leeds_data['new_data'] = json_encode($this->data['update']);
                            $leeds_data['ref_ids'] = implode(',', array_column($this->data['update'], 'id'));
                            DB::table('pas_user_activity')->insert($leeds_data);
                        }
                    }
                }
            }else {
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

    private function sendStudentEmail($student, $data_to_update_no_enc){
        if(!empty($student->partner_email) && !empty($student->email)){
            $placeholder['FIRSTNAME'] = $data_to_update_no_enc['first_name'];
            $placeholder['LASTNAME'] = $data_to_update_no_enc['last_name'];
            $placeholder['PHONE'] = $data_to_update_no_enc['phone'];
            $placeholder['EMAIL'] = $data_to_update_no_enc['email'];

            $email_req = new EmailRequest();
            $email_req->setTemplate(EmailTemplates::ZOHO_PERSONAL_DETAIL_UPDATED)
                ->setPlaceholder($placeholder)
                /*->setFromName($_ENV['FROM_NAME'])
                ->setFromEmail($_ENV['FROM_EMAIL'])*/
                ->setTo([[$student->partner_email, $student->partner_name]])
                //->setTo([['rajneesh@xoomwebdevelopment.com', 'Rajneesh']])
                ->setCc([[$student->email, $student->student_name]])
                ->setLogSave(true);

            $email_helper = new EmailHelper($email_req);
            $email_helper->sendEmail();
        }

    }

}
