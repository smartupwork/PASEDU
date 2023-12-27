<?php
namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\Student;
use App\Models\User;
use App\ZohoHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CronController extends Controller
{
    private $off_set = 0;
    private $limit = 200;
    private $page = 1;

    private $data = [
        'insert' => [],
        'update' => [],
    ];

    private $existing_enrollments = [];

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function importEnrollment(Request $request)
    {
        $our_db_enrollment = DB::table('pas_enrollment');
        if($request->partner){
            $our_db_enrollment->where('partner_zoho_id', '=', $request->partner);
        }

        $this->existing_enrollments = $our_db_enrollment->pluck('zoho_id')->toArray();
//dd($this->existing_enrollments);
        $this->getEnrollments($request);
        $data = $this->data;
        //dd($data);
        return view('cron.import-enrollment', compact('data'));
    }

    private function getEnrollments($request){
        //$before_1_hour = Carbon::now('GMT-5')->subMinutes(70)->format('c');
        $criteria = [];
        if(isset($request->partner) && !empty($request->partner)){
            $criteria = [
                ['Account_Name.id', 'equals', $request->partner],
                //['Status', 'equals', 'Expired'],
                //['Username', 'equals', 'joshuaro507@gmail.com'],
            ];
        }

        $zoho_response = ZohoHelper::getInstance()->fetchCriteria('Sales_Orders', ['Account_Name.id', 'Subject', 'Account_Name', 'Deal_Name', 'Status', 'Grand_Total', 'Start_Date', 'Program', 'Completion_Date', 'End_Date', 'Final_Grade', 'Username', 'Created_Time', 'Modified_Time'], $this->page, $this->limit, $criteria);
        //dd($zoho_response);
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
            foreach ($zoho_response['data'] as $enrollment) {
                if(!stristr($enrollment['Subject'],"test") && $enrollment['Status'] != 'Test_Kat.disregard' && $enrollment['Status'] != 'test-kat-disregard'){
                    $zoho_data = [
                        'zoho_id' => $enrollment['id'],
                        'student_id' => (isset($enrollment['Deal_Name']['id']) && isset($students_key[$enrollment['Deal_Name']['id']])) ? $students_key[$enrollment['Deal_Name']['id']]: null,
                        'student_zoho_id' => isset($enrollment['Deal_Name']['id']) ? $enrollment['Deal_Name']['id']:null,
                        'partner_id' => isset($partners_key[$enrollment['Account_Name']['id']]) ? $partners_key[$enrollment['Account_Name']['id']]: null,
                        'partner_zoho_id' => $enrollment['Account_Name']['id'],
                        'subject' => $enrollment['Subject'],
                        'status' => $enrollment['Status'],
                        'grand_total' => $enrollment['Grand_Total'],
                        'start_date' => $enrollment['Start_Date'],
                        'program_name' => isset($enrollment['Program']['name']) ? $enrollment['Program']['name']:null,
                        'program_zoho_id' => isset($enrollment['Program']['id']) ? $enrollment['Program']['id']:null,
                        'completion_date' => $enrollment['Completion_Date'],
                        'end_date' => $enrollment['End_Date'],
                        'final_grade' => $enrollment['Final_Grade'],
                        'username' => $enrollment['Username'],
                        'enrollment_created_at' => $enrollment['Created_Time'],
                        'enrollment_updated_at' => $enrollment['Modified_Time'],
                    ];

                    if(in_array($enrollment['id'], $this->existing_enrollments)){
                        $zoho_data['updated_at'] = date('Y-m-d H:i:s');
                        $this->data['update'][] = $zoho_data;
                    }else {
                        $zoho_data['created_at'] = date('Y-m-d H:i:s');
                        $this->data['insert'][] = $zoho_data;
                    }
                }

            }

            if ($zoho_response['info']['more_records']) {
                $this->off_set = $this->off_set == 0 ? $this->limit : $this->off_set + $this->limit;
                $this->page += 1;
                $this->getEnrollments($request);
                ///$this->info($partner['partner_name'].' find more.');
            }else{
                //dd($this->data);
                /*if(count($this->data['insert']) > 0){
                    foreach (array_chunk($this->data['insert'],1000) as $t) {
                        DB::table('pas_enrollment')->insert($t);
                    }
                }
                if(count($this->data['update']) > 0){
                    foreach (array_chunk($this->data['update'],1000) as $enrolls) {
                        foreach ($enrolls as $enroll) {
                            DB::table('pas_enrollment')->where('zoho_id', '=', $enroll['zoho_id'])->update($enroll);
                        }
                    }
                }*/
            }
        }
    }

    public function enrollmentLogin(){
        return view('auth-enrollment.login');
    }

    public function authEnrollment(Request $request){
        $criteria = [];
        if(empty($request->email) || empty($request->password)){
            return [
                'status' => false,
                'message' => 'Please enter email address and password'
            ];
        }

        if(!empty($request->email) && !empty($request->password)){
            $criteria = [
                ['Email', 'equals', $request->email],
                //['SalesOrders.Password', 'equals', $request->password],
                //['Username', 'equals', 'joshuaro507@gmail.com'],
            ];
        }

        $contact = ZohoHelper::getInstance()->fetchCriteria('Contacts', ['First_Name', 'Last_Name', 'Contact_Title', 'Email', 'SalesOrders.End_Date'], 1, 2, $criteria);

        if($contact['status'] == 'error'){
            return [
                'status' => false,
                'message' => $contact['message']
            ];
        }

        $contact_enrollment = ZohoHelper::getInstance()->fetchRelatedRecords('Contacts/'.$contact['data'][0]['id'], 'SalesOrders');
        if($contact_enrollment['status'] == 'error'){
            return [
                'status' => false,
                'message' => $contact_enrollment['message']
            ];
        }

        if($contact_enrollment['data']['data'][0]['Password'] == $request->password){
            return [
                'status' => true,
                'message' => 'Authentication successful',
                'data' => $contact_enrollment['data']['data'][0],
            ];
        }
        return [
            'status' => false,
            'message' => 'Authentication failed.'
        ];
    }

    public function fetchPartner(){
        $response = ZohoHelper::getInstance()->fetch('Accounts');
        dd($response);
    }

    public function createPartner(){
        dump(['CLIENT_ID' => $_ENV['ZOHO_CLIENT_ID'], 'SECRET' => $_ENV['ZOHO_CLIENT_SECRET']]);
        $data = [
            'Account_Name' => 'XWDS'
        ];
        $response = ZohoHelper::getInstance()->addRecordDebug([$data], 'Accounts');
        dd($response);
    }

    public function deletePartner(Request $request){
        $response = ZohoHelper::getInstance()->deleteRecords('Accounts', [$request->id]);
        dd($response);
    }

    /*public function createDeals(){
        $owner = DB::table('pas_owner')
            ->where('email', '=', $_ENV['KAT_OWNER_EMAIL'])
            ->first();

        $data = [
            'Account_Name' => [
                'id' => (string) User::getPartnerDetail('zoho_id'),
                'name' => User::getPartnerDetail('partner_name')
            ],
            'Deal_Name' => 'Test User',
            'Stage' => 'Active',
            // Program Information
            'Program_Id' => '',
            'Program' => [
                'name' => 'Certified Administrative Assistant with Office 2019',
                'id' => 1066248000463529011
            ],
            'Start_Date' => date('Y-m-d', strtotime($item['start_date'])),
            'End_Date' => date('Y-m-d', strtotime($item['end_date'])),
            'Primary_Amount' => $item['payment_amount'],
            'Collected_Amount' => $item['price_paid'],
            'Amount' => $item['price_paid'],
            'Payment_Type' => $item['payment_type'],
            'Email' => $item['email'],
            'Phone' => $item['phone'],
            'Street' => $item['street'],
            'City' => $item['city'],
            'State' => null,
            'Country' => null,
            'Zip' => $item['zip'],
            'Lead_Source' => 'Client Registration',
            'Owner' => [
                'id' => $owner ? $owner->zoho_id:null,
                'name' => $owner ? $owner->full_name:null,
            ],
            'Lead_Source_new' => 'Clients',
            'Lead_Segment' => 'PAS Site',
            'Lead_Origin' => 'PAS- Enrollment Form',
            'Lead_Status' => 'New',
            'Lead_Stage' => 'Active',
        ];

        $response = ZohoHelper::getInstance()->addRecord([$data], 'Deals');
        dd($response);
    }*/

    public function deleteDeals(Request $request){
        $response = ZohoHelper::getInstance()->deleteRecords('Deals', [$request->id]);
        dd($response);
    }

    public function createContact(){
        $data = [
            'Account_Name' => [
                'id' => (string) User::getPartnerDetail('zoho_id'),
                'name' => User::getPartnerDetail('partner_name')
            ],
            'Email' => 'xwds@testapi.com',
            'Contact_Active' => 'Active',
            'Phone' => '9999999999',
            'First_Name' => 'Khemraj',
            'Last_Name' => 'Maurya',
            'Contact_Role' => 'Student',
            'Mailing_Street' => 'Test Street',
            'Mailing_Country' => null,
            'Mailing_State' => null,
            'Mailing_City' => 'Gurgaon',
            'Mailing_Zip' => '220033',
        ];
        $response = ZohoHelper::getInstance()->addRecordDebug([$data], 'Contacts');
        dd($response);
    }

    public function deleteContact(Request $request){
        $response = ZohoHelper::getInstance()->deleteRecords('Contacts', [$request->id]);
        dd($response);
    }

    public function deleteLeads(Request $request){
        $response = ZohoHelper::getInstance()->deleteRecords('Leads', [$request->id]);
        dd($response);
    }

}
