<?php

namespace App\Console\Commands;

use App\EmailHelper;
use App\EmailRequest;
use App\Models\Partner;
use App\Models\Student;
use App\Utility;
use App\ZohoHelper;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class EnrollmentUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enrollmentUpdate:hook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enrollments Update with ZOHO Notification API';

    private $hook_table = 'pas_zoho_notification';

    private $module_name = 'Sales_Orders';

    private $table = 'pas_enrollment';

    private $log_data = ['insert' => [], 'update' => []];

    private $module_fields = [
        'Subject',
        'Account_Name',
        'Deal_Name',
        'Status',
        'Grand_Total',
        'Start_Date',
        'Program',
        'Completion_Date',
        'End_Date',
        'Final_Grade',
        'Username',
        'Created_Time',
        'Modified_Time',
        'Contact_Name'
    ];


    private $data = [
        'insert' => [],
        'update' => [],
        'delete' => [],
    ];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $hooks = DB::table($this->hook_table)->where([
            ['module', '=', $this->module_name],
            ['is_executed', '=', 0],
        ])
            ->limit(200)
            ->get()
            ->toArray();

        if(count($hooks) > 0){
            try{
                $partners = Partner::all()->toArray();
                $partners_key = array_column($partners, 'id', 'zoho_id');

                $students = Student::all()->toArray();
                $students_key = array_column($students, 'id', 'zoho_id');

                $existing_contacts = DB::table('pas_contact')->pluck('id', 'zoho_id')->toArray();

                $hook_action =[];
                //dd($hooks);
                $hooks_to_be_delete = array_column($hooks, 'id');
                //dd($hooks_to_be_delete);
                $hook_action['all'] = [];
                $hook_action['insert'] = [];
                $hook_action['update'] = [];
                $hook_action['delete'] = [];
                foreach ($hooks as $hook) {
                    if($hook->operation != 'delete'){
                        $hook_action['all'][] = $hook->ids;
                    }
                    $hook_action[$hook->operation][] = $hook->ids;
                }
                //dd($hook_action);

                $zoho_records = ZohoHelper::getInstance()->fetchByIds($this->module_name, array_unique($hook_action['all']), $this->module_fields);
    //dd($zoho_records);


                if(isset($zoho_records['data']) && count($zoho_records['data']) > 0){
                    foreach ($zoho_records['data'] as $zoho_record) {
                        if(stristr($zoho_record['Subject'],"test") || $zoho_record['Status'] == 'Test_Kat.disregard' || $zoho_record['Status'] == 'test-kat-disregard' && in_array($zoho_record['id'], $hook_action['update'])) {
                            $hook_action['delete'][] = $zoho_record['id'];
                        }else{
                            $data = [
                                'zoho_id' => $zoho_record['id'],
                                'student_id' => (isset($zoho_record['Deal_Name']['id']) && isset($students_key[$zoho_record['Deal_Name']['id']])) ? $students_key[$zoho_record['Deal_Name']['id']] : null,
                                'student_zoho_id' => isset($zoho_record['Deal_Name']['id']) ? $zoho_record['Deal_Name']['id'] : null,
                                'partner_id' => isset($partners_key[$zoho_record['Account_Name']['id']]) ? $partners_key[$zoho_record['Account_Name']['id']] : null,
                                'partner_zoho_id' => $zoho_record['Account_Name']['id'],
                                'subject' => addslashes($zoho_record['Subject']),
                                'status' => addslashes($zoho_record['Status']),
                                'grand_total' => $zoho_record['Grand_Total'],
                                'start_date' => $zoho_record['Start_Date'],
                                'program_name' => isset($zoho_record['Program']['name']) ? addslashes($zoho_record['Program']['name']) : null,
                                'program_zoho_id' => isset($zoho_record['Program']['id']) ? $zoho_record['Program']['id'] : null,
                                'completion_date' => $zoho_record['Completion_Date'],
                                'end_date' => $zoho_record['End_Date'],
                                'final_grade' => $zoho_record['Final_Grade'],
                                'username' => $zoho_record['Username'],
                                'enrollment_created_at' => $zoho_record['Created_Time'],
                                'enrollment_updated_at' => $zoho_record['Modified_Time'],
                            ];

                            if(isset($zoho_record['Contact_Name']['id'])){
                                if(isset($existing_contacts[$zoho_record['Contact_Name']['id']])){
                                    $data['contact_id'] = $existing_contacts[$zoho_record['Contact_Name']['id']];
                                }
                                $data['contact_zoho_id'] = $zoho_record['Contact_Name']['id'];
                            }

                            if (count($hook_action['insert']) > 0 && in_array($zoho_record['id'], $hook_action['insert'])) {
                                $data['created_at'] = date('Y-m-d H:i:s');
                                $this->data['insert'][] = $data;
                                $this->log_data['insert'][] = $data;
                                $this->log_data['insert_ids'][] = $zoho_record['id'];
                            } else if (count($hook_action['update']) > 0 && in_array($zoho_record['id'], $hook_action['update'])) {
                                $data['updated_at'] = date('Y-m-d H:i:s');
                                $this->data['update'][] = $data;
                                $this->log_data['update'][] = $data;
                                $this->log_data['update_ids'][] = $zoho_record['id'];
                            }
                        }
                    }
                }else{
                    $this->warn('There are not update or insert program.');
                }

                //dd($this->data);

                if (count($this->data['insert']) > 0) {
                    foreach ($this->data['insert'] as $data) {
                        $is_exists = DB::table($this->table)
                            ->where('zoho_id', '=', $data['zoho_id'])->get()->first();
                        if($is_exists){
                            DB::table($this->table)
                                ->where('id', '=', $is_exists->id)
                                ->update($data);
                        }else{
                            DB::table($this->table)->insert($data);
                        }
                    }

                }

                if (count($this->data['update']) > 0) {
                    foreach ($this->data['update'] as $hook_update) {
                        DB::table($this->table)
                            ->where('zoho_id', '=', $hook_update['zoho_id'])
                            ->update($hook_update);
                    }
                }

                dump($hook_action['delete']);
                if (count($hook_action['delete']) > 0) {
                    DB::table($this->table)->whereIn('zoho_id', $hook_action['delete'])->delete();
                }

                dump($hooks_to_be_delete);
                if(count($hooks_to_be_delete) > 0){
                    DB::table($this->hook_table)->whereIn("id", $hooks_to_be_delete)->delete();
                }


                $leeds_data['action_via'] = 'cron';
                $leeds_data['url'] = 'cron-enrollment';
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


                $this->info('Total records Inserted('.count($this->data['insert']).') and Updated('.count($this->data['update']).') and Deleted('.count($hook_action['delete']).').');
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
        }else{
            $this->info('Data not found to update/insert/delete into notification API.');
        }


    }
}
