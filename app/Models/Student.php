<?php

namespace App\Models;

use App\EmailHelper;
use App\EmailRequest;
use App\ZohoHelper;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Student extends Model
{
    use HasFactory;

    const STATUS_ACTIVE     = 1;
    const STATUS_COMPLETE   = 2;
    const STATUS_REFUND     = 3;
    const STATUS_EXPIRED    = 4;

    const PT_AUGUSOFT  = "Augusoft";
    const PT_MyCAA = "MyCAA";
    const PT_VOC_REHAB = "Voc Rehab";
    const PT_EMPLOYER = "Employer";
    const PT_WIOA = "WIOA";
    const PT_GRANT = "Grant";
    const PT_SELF_PAY = "Self-Pay";
    const PT_MILITARY_TA = "Military TA";
    const PT_N_A_WORLD_EDUCATION = "N/A- World Education";
    const PT_CCI = "CCI";
    const PT_AUGUSOFT_MYCAA = "Augusoft- MyCAA";
    const PT_AUGUSOFT_VOCREHAB = "Augusoft- VocRehab";
    const PT_TAA = "TAA";
    const PT_SALLIE_MAE = "Sallie Mae";
    const PT_GI_BILL = "GI Bill";
    const PT_SCHOOL_CLIENT_STAFF = "School/Client Staff";
    const PT_AIR_FORCE_COOL = "Air Force Cool";
    const PT_OTHER = "Other";

    protected $table = 'pas_student';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'first_name',
        'last_name',
        'email',
        'program_id',
        'start_date',
        'complete_date',
        'status',
        'payment_type',
        'end_date',
        'phone',
        'street',
        'city',
        'state',
        'country',
        'zip_code',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];

    /**
     * @return array
     */
    public static function getPaymentType($index = null){
        $pick_list_values = DB::table('zoho_field_meta')
            ->where('module', '=', 'Payment Type')
            ->value('pick_list_values');

        $pick_list_values_arr = [];

        if(!empty($pick_list_values)){
            $pick_list_values_arr = json_decode($pick_list_values, true);
        }

        if($index != ''){
            if(isset($pick_list_values_arr[$index])){
                return $pick_list_values_arr[$index]['actual_value'];
            }
            return null;
        }

        return $pick_list_values_arr;

        /*$status = [
            self::PT_AUGUSOFT,
            self::PT_MyCAA,
            self::PT_VOC_REHAB,
            self::PT_EMPLOYER,
            self::PT_WIOA,
            self::PT_GRANT,
            self::PT_SELF_PAY,
            self::PT_MILITARY_TA,
            self::PT_N_A_WORLD_EDUCATION,
            self::PT_CCI,
            self::PT_AUGUSOFT_MYCAA,
            self::PT_AUGUSOFT_VOCREHAB,
            self::PT_TAA,
            self::PT_SALLIE_MAE,
            self::PT_GI_BILL,
            self::PT_SCHOOL_CLIENT_STAFF,
            self::PT_AIR_FORCE_COOL,
            self::PT_OTHER,
        ];

            if($index != '' && isset($status[$index])){
                return $status[$index];
            }
            return $status;*/
    }

    /**
     * @return array
     */
    public static function rules($states_arr, $countries_arr){
        $programs_arr = [];
        $programs = Program::where('status', '=', 'Active')->get();
        foreach($programs as $program){
            $programs_arr[] = $program['id'];
        }
        $payment_type = array_column(self::getPaymentType(), 'actual_value');
        return [
                    'first_name' => 'bail|required|min:2|max:100',
                    'last_name' => 'bail|required|min:2|max:100',
                    'partner_id' => 'required|integer',
                    'program_id' => 'required|integer|in:' . implode(',', $programs_arr),
                    'payment_type' => 'required|in:' . implode(',', array_map('strtolower', $payment_type)),
                    'price_paid' => 'required|numeric',
                    'start_date' => 'required|date_format:m/d/Y',
                    'end_date' => 'nullable|date_format:m/d/Y|after_or_equal:start_date',
                    'phone' => 'required|min:5|max:20',
                    'street' => 'required|string|min:2|max:255',
                    'city' => 'required|string|min:2|max:150',
                    'state' => 'required|integer|in:' . implode(',', $states_arr),
                    'zip' => 'required|string|min:5|max:20',
                    'country' => 'required|integer|in:' . implode(',', $countries_arr),
                    'email' => [
                        'required',
                        'email',
                        //'email_program_unique'
                    ],

            ];
    }

    /**
     * @return array
     *
     *  This method only for validation message masking purpose
     */
    public static function attributeNames($key = ''){
        $attributes = [
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'email' => 'Email',
            'partner_id' => 'Partner',
            'program_id' => 'Program',
            'payment_type' => 'Payment Type',
            'price_paid' => 'Price Paid',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'phone' => 'Phone',
            'street' => 'Street',
            'city' => 'City',
            'state' => 'State',
            'zip' => 'Zip',
            'country' => 'Country',
        ];
        if($key !='' && isset($attributes[$key])){
            return $attributes[$key];
        }
        return $attributes;
    }

    /*public function program() {
        return $this->hasOne('App\Models\Program','id');
    }*/

    public static function getDummyData($isLeads = false){
        return [
            1 => [
                'A' => 'First Name',
                'B' => 'Last Name',
                'C' => 'Email',
                'D' => 'Program',
                'E' => 'Price Paid',
                'F' => 'Payment Type',
                'G' => 'Start Date',
                'H' => 'End Date',
                'I' => 'Phone',
                'J' => 'Street',
                'K' => 'City',
                'L' => 'State',
                'M' => 'ZIP',
                'N' => 'Country',
            ],
            2 => [
                'A' => Factory::create()->firstName,
                'B' => Factory::create()->lastName,
                'C' => $isLeads ? 'example@gmail.com': Factory::create()->unique()->safeEmail,
                'D' => 'Certified Medical Biller and Coder-Pierpont Only',
                'E' => 1,
                'F' => 'Voc Rehab',
                'G' => Carbon::now()->addDays(10)->format('m/d/Y'),
                'H' => '',
                'I' => Factory::create()->phoneNumber,
                'J' => 'Test Street',
                'K' => 'Test City',
                'L' => 'Alaska',
                'M' => '123456',
                'N' => 'United states',
            ],
        ];
    }

    public function sendStudentEnrollmentEmail($zoho_data){
        $placeholder['FIRST_NAME'] = $zoho_data['First_Name'];
        $placeholder['LAST_NAME'] = $zoho_data['Last_Name'];
        $placeholder['PARTNER_NAME'] = User::getPartnerDetail('partner_name');
        $placeholder['PROGRAM_NAME'] = isset($zoho_data['Program']['name']) ? $zoho_data['Program']['name']:'-';
        $placeholder['EMAIL'] = $zoho_data['Email'];
        $placeholder['PAYMENT_TYPE'] = $zoho_data['Payment_Method'];
        $placeholder['START_DATE'] = $zoho_data['Start_Date'];
        $placeholder['END_DATE'] = $zoho_data['End_Date'];
        $placeholder['PHONE'] = $zoho_data['Phone'];
        $placeholder['REQUESTER_NAME'] = Auth::user()->firstname.' '.Auth::user()->lastname;
        $placeholder['REQUESTER_EMAIL'] = Auth::user()->email;

        $email_req = new EmailRequest();
        $email_req->setTemplate(EmailTemplates::STUDENT_ENROLLMENT)
            ->setPlaceholder($placeholder)
            ->setTo([
                [$_ENV['ADMIN_EMAIL_FIRST'], 'PAS Admin'],
                [$_ENV['ADMIN_EMAIL_SECOND'], 'PAS Admin'],
                [$_ENV['ENROLLMENT_NOTIFICATION'], 'PAS Admin'],
                [Auth::user()->email, 'PAS Admin'],
            ])
            ->setLogSave(true);

        $email_helper = new EmailHelper($email_req);
        $email_helper->sendEmail();
    }

    public function sendBulkStudentEnrollmentEmail($total_records){
        if($total_records > 0){
            $placeholder['SUCCESS_RECORDS'] = $total_records;
            $placeholder['PARTNER_NAME'] = User::getPartnerDetail('partner_name');

            $to_emails = [
                [$_ENV['ADMIN_EMAIL_FIRST'], 'PAS Admin'],
                [$_ENV['ADMIN_EMAIL_SECOND'], 'PAS Admin'],
                [$_ENV['HELP_DESK_EMAIL'], 'PAS Admin'],
                [Auth::user()->email, Auth::user()->firstname.' '.Auth::user()->lastname],
            ];

            $this->sendEmail(EmailTemplates::STUDENT_BULK_ENROLLMENT, $to_emails, $placeholder);

            /*$email_req = new EmailRequest();
            $email_req->setTemplate(EmailTemplates::STUDENT_BULK_ENROLLMENT)
                ->setPlaceholder($placeholder)
                ->setTo([
                    [$_ENV['ADMIN_EMAIL_FIRST'], 'PAS Admin'],
                    [$_ENV['ADMIN_EMAIL_SECOND'], 'PAS Admin'],
                    [$_ENV['HELP_DESK_EMAIL'], 'PAS Admin'],
                    [Auth::user()->email, Auth::user()->firstname.' '.Auth::user()->lastname],
                ])
                ->setCc([
                    [$_ENV['PARTNER_EMAIL'], 'PAS Admin']
                ])
                ->setLogSave(true);

            $email_helper = new EmailHelper($email_req);
            $email_helper->sendEmail();*/
        }
    }

    /**
     * @param $lead
     * @param $lead_owner_email
     */
    public function sendStudentLeadConvertToEnrollmentEmail($student_enr, $lead_owner_email){
        $placeholder['STUDENT_NAME'] = $student_enr['student_name'];
        $placeholder['STUDENT_EMAIL'] = $student_enr['email'];
        $placeholder['PROGRAM_NAME'] = $student_enr['program_name'];
        $placeholder['INSTITUTION'] = $student_enr['partner_name'];

        $placeholder['PRICE_PAID'] = $student_enr['price_paid'];
        $placeholder['PAYMENT_TYPE'] = $student_enr['payment_type'];
        $placeholder['START_DATE'] = $student_enr['start_date'];
        $placeholder['END_DATE'] = $student_enr['end_date'];
        $placeholder['PHONE'] = $student_enr['phone'];
        $placeholder['STREET'] = $student_enr['street'];
        $placeholder['CITY'] = $student_enr['city'];
        $placeholder['STATE'] = $student_enr['state'];
        $placeholder['ZIP'] = $student_enr['zip'];
        $placeholder['COUNTRY'] = $student_enr['country'];

        $email_req = new EmailRequest();
        $email_req->setTemplate(EmailTemplates::LEADS_CONVERTED_TO_SALE)
            ->setPlaceholder($placeholder)
            ->setTo([
                [$_ENV['ENROLLMENT_NOTIFICATION'], 'PAS Admin'],
                [$_ENV['ADMISSION_EMAIL'], 'PAS Admin'],
                [$lead_owner_email, 'PAS Admin'],
                //[$_ENV['HELP_DESK_EMAIL'], 'PAS Admin'],
                [Auth::user()->email, Auth::user()->firstname.' '.Auth::user()->lastname],
            ])
            ->setCc([
                [$_ENV['PARTNER_EMAIL'], 'PAS Admin']
            ])
            ->setLogSave(true);

        $email_helper = new EmailHelper($email_req);
        if($email_helper->sendEmail()){
            Log::channel('enrollment')->info('Email sent template ID: '.EmailTemplates::LEADS_CONVERTED_TO_SALE);
        }else{
            Log::channel('enrollment')->info('Email sending failed template ID: '.EmailTemplates::LEADS_CONVERTED_TO_SALE);
        }
    }

    /**
     * @param $student
     */
    public function sendStudentEnrollmentEmailNew($student){
        $placeholder['STUDENT_NAME'] = $student['student_name'];
        $placeholder['STUDENT_EMAIL'] = $student['email'];
        $placeholder['PROGRAM_NAME'] = $student['program_name'];
        $placeholder['INSTITUTION'] = $student['partner_name'];

        $placeholder['PRICE_PAID'] = $student['price_paid'];
        $placeholder['PAYMENT_TYPE'] = $student['payment_type'];
        $placeholder['START_DATE'] = $student['start_date'];
        $placeholder['END_DATE'] = $student['end_date'];
        $placeholder['PHONE'] = $student['phone'];
        $placeholder['STREET'] = $student['street'];
        $placeholder['CITY'] = $student['city'];
        $placeholder['STATE'] = $student['state'];
        $placeholder['ZIP'] = $student['zip'];
        $placeholder['COUNTRY'] = $student['country'];

        $to_emails = [
            //[$_ENV['REGISTRAR_EMAIL'], 'PAS Admin'],
            [$_ENV['ENROLLMENT_NOTIFICATION'], 'PAS Admin'],
            [$_ENV['HELP_DESK_EMAIL'], 'PAS Admin'],
            [Auth::user()->email, Auth::user()->firstname.' '.Auth::user()->lastname],
        ];

        if($this->sendEmail(EmailTemplates::STUDENT_ENROLLMENT_NEW, $to_emails, $placeholder)){
            Log::channel('enrollment')->info('Email sent successfully template id: '. EmailTemplates::STUDENT_ENROLLMENT_NEW);
        }else{
            Log::channel('enrollment')->info('Email sending failed template id: '. EmailTemplates::STUDENT_ENROLLMENT_NEW);
        }


        /*$email_req = new EmailRequest();
        $email_req->setTemplate(EmailTemplates::STUDENT_ENROLLMENT_NEW)
            ->setPlaceholder($placeholder)
            ->setTo([
                [$_ENV['REGISTRAR_EMAIL'], 'PAS Admin'],
                [$_ENV['ENROLLMENT_NOTIFICATION'], 'PAS Admin'],
                [$_ENV['HELP_DESK_EMAIL'], 'PAS Admin'],
                [Auth::user()->email, Auth::user()->firstname.' '.Auth::user()->lastname],
            ])
            ->setCc([
                [$_ENV['PARTNER_EMAIL'], 'PAS Admin']
            ])
            ->setLogSave(true);

        $email_helper = new EmailHelper($email_req);
        $email_helper->sendEmail();*/
    }

    public function sendBulkStudentLeadConvertToEnrollmentEmail($total_records){
        if($total_records > 0){
            $placeholder['SUCCESS_RECORDS'] = $total_records;
            $placeholder['PARTNER_NAME'] = User::getPartnerDetail('partner_name');

            $email_req = new EmailRequest();
            $email_req->setTemplate(EmailTemplates::STUDENT_BULK_ENROLLMENT)
                ->setPlaceholder($placeholder)
                ->setTo([
                    [$_ENV['ADMIN_EMAIL_FIRST'], 'PAS Admin'],
                    [$_ENV['ADMIN_EMAIL_SECOND'], 'PAS Admin'],
                ])
                ->setLogSave(true);

            $email_helper = new EmailHelper($email_req);
            $email_helper->sendEmail();
        }
    }

    /**
     * @param $item
     * @param $program_zid_name
     * @param $program_id_name
     * @param $states
     * @param $countries
     * @return array
     */
    public static function loadLeadsData($item, $lead, $program_zid_name, $program_id_name, $states, $countries){
        $data['id'] = $lead['data'][0]['id'];
        $data['School_Id'] = (string) User::getPartnerDetail('zoho_id');
        $data['School']['id'] = (string) User::getPartnerDetail('zoho_id');
        $data['School']['name'] = User::getPartnerDetail('partner_name');

        // Student Lead Information
        $data['First_Name'] = empty($lead['data'][0]['First_Name']) ? $item['first_name']:$lead['data'][0]['First_Name'];
        $data['Last_Name'] = empty($lead['data'][0]['Last_Name']) ? $item['last_name']:$lead['data'][0]['Last_Name'];
        $data['Email'] = empty($lead['data'][0]['Email']) ? $item['email']:$lead['data'][0]['Email'];
        $data['Phone'] = empty($lead['data'][0]['Phone']) ? $item['phone']:$lead['data'][0]['Phone'];

        // Program Information
        if(empty($lead['data'][0]['Program'])){
            $data['Program_Id'] = !empty($item['program_id']) ? $program_zid_name[$item['program_id']]:null;
            $data['Program']['id'] = !empty($item['program_id']) ? $program_zid_name[$item['program_id']]:null;
            $data['Program']['name'] =  !empty($item['program_id']) ? $program_id_name[$item['program_id']]:null;
        }else{
            $data['Program_Id'] = $lead['data'][0]['Program_Id'];
            $data['Program']['id'] = isset($lead['data'][0]['Program']['id']) ? $lead['data'][0]['Program']['id']:null;
            $data['Program']['name'] =  isset($lead['data'][0]['Program']['name']) ? $lead['data'][0]['Program']['name']:null;
        }


        // Amount Field not Available Into ZOHO
        //$data['Payment_Amount'] = empty($lead['data'][0]['Payment_Amount']) ? $lead['data'][0]['Payment_Amount']:$item['price_paid'];
        $data['Payment_Method'] = empty($lead['data'][0]['Payment_Method']) ? $item['payment_type']: $lead['data'][0]['Payment_Method'];

        if(empty($lead['data'][0]['Start_Date'])){
            $data['Start_Date'] = !empty($item['start_date']) ? $item['start_date']:null;
        }else{
            $data['Start_Date'] = $lead['data'][0]['Start_Date'];
        }

        if(empty($lead['data'][0]['End_Date'])) {
            $data['End_Date'] = !empty($item['end_date']) ? $item['end_date'] : null;
        }else{
            $data['End_Date'] = $lead['data'][0]['End_Date'];
        }

        // Address Information
        $data['Street'] = empty($lead['data'][0]['Street']) ? $item['street']:$lead['data'][0]['Street'];
        $data['City'] = empty($lead['data'][0]['City']) ? $item['city']:$lead['data'][0]['City'];
        if(empty($lead['data'][0]['State'])){
            $data['State'] = (isset($item['state']) && isset($states[$item['state']])) ? $states[$item['state']]:null;
        }else{
            $data['State'] = $lead['data'][0]['State'];
        }

        if(empty($lead['data'][0]['Country'])) {
            $data['Country'] = (isset($item['country']) && isset($countries[$item['country']])) ? $countries[$item['country']] : null;
        }else{
            $data['Country'] = $lead['data'][0]['Country'];
        }

        $data['Zip_Code'] = empty($lead['data'][0]['Zip_Code']) ? $item['zip']:$lead['data'][0]['Zip_Code'];

        // Default Values
        //$data['Lead_Source'] = 'Client Registration';
        $data['Owner_Email'] = isset($lead['data'][0]['Owner']['email']) ? $lead['data'][0]['Owner']['email']: null;
        //$data['Lead_Source_new'] = 'Clients';
        //$data['Lead_Segment'] = 'PAS Site';
        //$data['Lead_Origin'] = 'PAS- Enrollment Form';
        $data['Lead_Status'] = 'Enrollment Scheduled';
        $data['Lead_Stage'] = 'Active';

        return $data;

        /*return [
            'id' => $lead['data'][0]['id'],
            'School_Id' => (string) User::getPartnerDetail('zoho_id'),
            'School' => [
                'name' => User::getPartnerDetail('partner_name'),
                'id' => (string) User::getPartnerDetail('zoho_id')
            ],
            // Student Lead Information
            'First_Name' => $item['first_name'],
            'Last_Name' => $item['last_name'],
            'Email' => $item['email'],
            'Phone' => $item['phone'],

            // Program Information
            'Program_Id' => !empty($item['program_id']) ? $program_zid_name[$item['program_id']]:null,
            'Program' => [
                'name' => !empty($item['program_id']) ? $program_id_name[$item['program_id']]:null,
                'id' => !empty($item['program_id']) ? $program_zid_name[$item['program_id']]:null
            ],
            //'Payment_Amount' => !empty($item['program_id']) ? $program_unit_price[$item['program_id']]:null, // Amount Field not Available Into ZOHO
            'Payment_Amount' => $item['price_paid'],
            'Payment_Method' => $item['payment_type'],
            'Start_Date' => !empty($item['start_date']) ? $item['start_date']:null,
            'End_Date' => !empty($item['end_date']) ? $item['end_date']: null,

            // Address Information
            'Street' => $item['street'],
            'City' => $item['city'],
            'State' => (isset($item['state']) && isset($states[$item['state']])) ? $states[$item['state']]:null,
            'Country' => (isset($item['country']) && isset($countries[$item['country']])) ? $countries[$item['country']]:null,
            'Zip_Code' => $item['zip'],

            // Default Values
            'Lead_Source' => 'Client Registration',
            'Owner_Email' => isset($lead['data'][0]['Owner']['email']) ? $lead['data'][0]['Owner']['email']: null,
            'Lead_Source_new' => 'Clients',
            'Lead_Segment' => 'PAS Site',
            'Lead_Origin' => 'PAS- Enrollment Form',
            'Lead_Status' => 'New',
            'Lead_Stage' => 'Active',
        ];*/
    }

    /**
     * @param $item
     * @param $program_zid_name
     * @param $program_id_name
     * @param $states
     * @param $countries
     * @return array
     */
    public static function loadDealsData($item, $program_zid_name, $program_id_name, $states, $countries){
        $owner = DB::table('pas_owner')
            ->where('email', '=', $_ENV['KAT_OWNER_EMAIL'])
            ->first();

        return [
            'Account_Name' => [
                'id' => (string) User::getPartnerDetail('zoho_id'),
                'name' => User::getPartnerDetail('partner_name')
            ],
            'Deal_Name' => $item['first_name'].' '.$item['last_name'],
            // Program Information
            'Program_Id' => !empty($item['program_id']) ? $program_zid_name[$item['program_id']]:null,
            'Program' => [
                'name' => !empty($item['program_id']) ? $program_id_name[$item['program_id']]:null,
                'id' => !empty($item['program_id']) ? $program_zid_name[$item['program_id']]:null
            ],
            'Start_Date' => !empty($item['start_date']) ? date('Y-m-d', strtotime($item['start_date'])):null,
            'End_Date' => !empty($item['end_date']) ? date('Y-m-d', strtotime($item['end_date'])):null,
            'Primary_Amount' => 0,// $item['payment_amount']
            'Total_Amount' => 0,// $item['payment_amount']
            'Collected_Amount' => $item['price_paid'],
            'Amount' => $item['price_paid'],
            'Payment_Type' => $item['payment_type'],
            'Email' => $item['email'],
            'Phone' => $item['phone'],
            'Street' => $item['street'],
            'City' => $item['city'],
            'State' => (isset($item['state']) && isset($states[$item['state']])) ? $states[$item['state']]:null,
            'Country' => (isset($item['country']) && isset($countries[$item['country']])) ? $countries[$item['country']]:null,
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
            'Stage' => 'Ready For Enrollment',
        ];
    }

    public static function loadContactData($item, $states, $countries){
        return [
            'Account_Name' => [
                'id' => (string) User::getPartnerDetail('zoho_id'),
                'name' => User::getPartnerDetail('partner_name')
            ],
            'Email' => $item['email'],
            'Contact_Active' => 'Active',
            'First_Name' => $item['first_name'],
            'Last_Name' => $item['last_name'],
            'Contact_Role' => 'Student',
            'Phone' => $item['phone'],
            'Mailing_Street' => $item['street'],
            'Mailing_Country' => (isset($item['country']) && isset($countries[$item['country']])) ? $countries[$item['country']]:null,
            'Mailing_State' => (isset($item['state']) && isset($states[$item['state']])) ? $states[$item['state']]:null,
            'Mailing_City' => $item['city'],
            'Mailing_Zip' => $item['zip'],
        ];

    }

    public function sendEmail($template_id, $to_emails, $placeholder){
        $email_req = new EmailRequest();
        $email_req->setTemplate($template_id)
            ->setPlaceholder($placeholder)
            ->setTo($to_emails)
            ->setCc([
                [$_ENV['PARTNER_EMAIL'], 'PAS Admin']
            ])
            ->setLogSave(true);

        $email_helper = new EmailHelper($email_req);
        $email_helper->sendEmail();
    }

    public static function getDealWithCriteria($email, $program_zoho_id){
        $criteria = [
            ['Email', 'equals', $email],
            ['Program.id', 'equals', $program_zoho_id],
        ];

        $deal = ZohoHelper::getInstance()->fetchCriteria('Deals', [], 1, 1, $criteria);

        if (isset($deal['status']) && $deal['status'] == 'error') {
            return ['status' => 'fail', 'errors' => $deal['message']];
        }

        return ['status' => 'success', 'data' => $deal['data']];
    }
}
