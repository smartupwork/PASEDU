<?php

namespace App\Console\Commands;

use App\Models\Partner;
use App\Models\Student;
use App\ZohoHelper;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportEnrollment extends Command
{
    const OFF_SET = 0;
    const LIMIT = 200;

    private $off_set = self::OFF_SET;
    private $limit = self::LIMIT;
    private $page = 1;

    private $data = [
        'insert' => [],
        'update' => [],
    ];

    private $existing_enrollments = [];
    private $existing_contacts = [];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'importEnrollment:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Enrollment from ZOHO server. Cron should be run every hours.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $our_db_enrollment = DB::table('pas_enrollment')->get()->all();
        $this->existing_enrollments = array_column($our_db_enrollment, 'zoho_id');
        //dd($this->existing_enrollments);

        $this->existing_contacts = DB::table('pas_contact')->pluck('id', 'zoho_id')->toArray();

        $this->getEnrollments();
        $this->info('Total records Inserted('.count($this->data['insert']).') and Updated('.count($this->data['update']).').');
    }

    private function getEnrollments(){
        //$before_1_hour = Carbon::now('GMT-5')->subMinutes(70)->format('c');

        $zoho_response = ZohoHelper::getInstance()->fetch('Sales_Orders', ['Subject', 'Account_Name', 'Deal_Name', 'Status', 'Grand_Total', 'Start_Date', 'Program', 'Completion_Date', 'End_Date', 'Final_Grade', 'Username', 'Created_Time', 'Modified_Time', 'Contact_Name'], $this->page, $this->limit);

        if($zoho_response['status'] == 'error'){
            $this->error($zoho_response['message']);
            die;
        }

        $partners = Partner::all()->toArray();
        $partners_key = array_column($partners, 'id', 'zoho_id');

        $students = Student::all()->toArray();
        $students_key = array_column($students, 'id', 'zoho_id');
        //dd($students_key);
        if(count($zoho_response['data']) > 0){
            foreach ($zoho_response['data']['data'] as $enrollment) {
                if(!stristr($enrollment['Subject'],"test") && $enrollment['Status'] != 'Test_Kat.disregard' && $enrollment['Status'] != 'test-kat-disregard'){
                    $zoho_data = [
                        'zoho_id' => $enrollment['id'],
                        'student_id' => (isset($enrollment['Deal_Name']['id']) && isset($students_key[$enrollment['Deal_Name']['id']])) ? $students_key[$enrollment['Deal_Name']['id']]: null,
                        'student_zoho_id' => isset($enrollment['Deal_Name']['id']) ? $enrollment['Deal_Name']['id']:null,
                        'partner_id' => isset($partners_key[$enrollment['Account_Name']['id']]) ? $partners_key[$enrollment['Account_Name']['id']]: null,
                        'partner_zoho_id' => $enrollment['Account_Name']['id'],
                        'subject' => addslashes($enrollment['Subject']),
                        'status' => addslashes($enrollment['Status']),
                        'grand_total' => $enrollment['Grand_Total'],
                        'start_date' => $enrollment['Start_Date'],
                        'program_name' => isset($enrollment['Program']['name']) ? addslashes($enrollment['Program']['name']):null,
                        'program_zoho_id' => isset($enrollment['Program']['id']) ? $enrollment['Program']['id']:null,
                        'completion_date' => $enrollment['Completion_Date'],
                        'end_date' => $enrollment['End_Date'],
                        'final_grade' => $enrollment['Final_Grade'],
                        'username' => $enrollment['Username'],
                        'enrollment_created_at' => $enrollment['Created_Time'],
                        'enrollment_updated_at' => $enrollment['Modified_Time'],
                        'contact_id' => null,
                        'contact_zoho_id' => null,
                    ];

                    if(isset($enrollment['Contact_Name']['id'])){
                        if(isset($this->existing_contacts[$enrollment['Contact_Name']['id']])){
                            $zoho_data['contact_id'] = $this->existing_contacts[$enrollment['Contact_Name']['id']];
                        }
                        $zoho_data['contact_zoho_id'] = $enrollment['Contact_Name']['id'];
                    }

                    if(in_array($enrollment['id'], $this->existing_enrollments)){
                        $zoho_data['updated_at'] = date('Y-m-d H:i:s');
                        $this->data['update'][] = $zoho_data;
                    }else {
                        $zoho_data['created_at'] = date('Y-m-d H:i:s');
                        $this->data['insert'][] = $zoho_data;
                    }
                }

            }

            if ($zoho_response['data']['info']['more_records']) {
                $this->off_set = $this->off_set == 0 ? $this->limit : $this->off_set + $this->limit;
                $this->page += 1;
                $this->getEnrollments();
                ///$this->info($partner['partner_name'].' find more.');
            }else{
                if(count($this->data['insert']) > 0){
                    foreach (array_chunk($this->data['insert'],1000) as $t) {
                        Db::table('pas_enrollment')->insert($t);
                    }
                }
                if(count($this->data['update']) > 0){
                    foreach (array_chunk($this->data['update'],1000) as $enrolls) {
                        foreach ($enrolls as $enroll) {
                            Db::table('pas_enrollment')->where('zoho_id', '=', $enroll['zoho_id'])->update($enroll);
                        }
                    }
                }
            }
        }
    }
}
