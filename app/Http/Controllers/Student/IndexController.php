<?php
namespace App\Http\Controllers\Student;
use App\EmailHelper;
use App\EmailRequest;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\EmailTemplates;
use App\Models\Partner;
use App\Models\Program;
use App\Models\State;
use App\Models\Student;
use App\Models\ListingSetting;
use App\Models\User;
use App\Models\UserAccess;
use App\Models\UserNotification;
use App\UserActivityHelper;
use App\Utility;
use App\ZohoHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Exception;
use Session;
use Config;
use Lang;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Mpdf\Mpdf;
use Cookie;

require base_path("vendor/autoload.php");

class IndexController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if(!UserAccess::hasAccess(UserAccess::STUDENT_MANAGEMENT_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $sort_column = 's.start_date';
        $sort_order = 'asc';
        $column_setting = ListingSetting::getStudentEnrollmentDefaultListing();
        return view('student.index', compact('column_setting', 'sort_column', 'sort_order'));
    }

    public function add(){
        //$zoho_response = ZohoHelper::getInstance()->fetch('Contacts', ['Contact_Name', 'First_Name', 'Last_Name', 'Contact_Title', 'DOB', 'Email', 'Mobile', 'Phone', 'Contact_Active', 'Contact_Role', 'Lead_Created', 'Lead_Source', 'Mailing_City', 'Mailing_Country', 'Mailing_State', 'Mailing_Street', 'Mailing_Zip', 'Account_Name', 'Secondary_Email', 'Social_Security_Num'], 1, 2);

//dd($zoho_response);
        //$zoho_response = ZohoHelper::getInstance()->fetch('Deals', ['Contact_Name', 'Account_Name', 'Deal_Name', 'Amount', 'Stage', 'Start_Date', 'Email', 'Phone', 'Street', 'City', 'State', 'Zip', 'Country'], 1,3);
        //dd($zoho_response);
        if(!UserAccess::hasAccess(UserAccess::STUDENT_MANAGEMENT_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $programs = Program::where('status', '=', 'Active')->orderBy('name', 'ASC')->get();
        $states = State::where('status', '=', 1)->get();
        $countries = Country::where('status', '=', 1)->get();
        return view('student.create', compact('programs', 'states', 'countries'));
    }

    public function store(Request $request){
        if(!UserAccess::hasAccess(UserAccess::STUDENT_MANAGEMENT_ACCESS, 'add')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        Log::channel('enrollment')->info('CREATE NEW ENROLLMENT START');

        $pas_data = [];
        $errors = [];
        $unique_check = [];
        $get_unique_errors = [];

        if(isset($request->student) && count($request->student) > 0){

            // Fetch Master data for Get Label for add/update into ZOHO
            $programs = Program::where('status', '=', 'Active')->get()->toArray();
            $program_id_name = array_column($programs, 'name', 'id');
            $program_zid_name = array_column($programs, 'zoho_id', 'id');
            $program_unite_price = array_column($programs, 'unite_price', 'id');

            $all_states = State::where('status', '=', 1)->get()->toArray();
            $all_countries = Country::where('status', '=', 1)->get()->toArray();
            $states = array_column($all_states, 'state_name', 'id');
            $countries = array_column($all_countries, 'country_name', 'id');

            $states_arr = array_column($all_states, 'id', 'state_name');
            $countries_arr = array_column($all_countries, 'id', 'country_name');

            Log::channel('enrollment')->info('Validate data START');

            foreach ($request->student as $index => $item) {
                $item['partner_id'] = User::getPartnerDetail('id');
                $item['payment_type'] = strtolower($item['payment_type']);

                // Validate each record and separate ZOHO Leads/Deals and PAS pas_student/pas_schedule table data
                /*Validator::extend('email_program_unique', function ($attribute, $value, $parameters, $validator) use ($item) {
                    $count = DB::table('pas_student')->where(DB::raw('AES_DECRYPT(email, "' . $_ENV['AES_ENCRYPT_KEY'] . '")'), $item['email'])->where('program_id', $item['program_id'])->count();
                    //$validator->errors()->add($attribute, 'Email address('.$item['email'].') and program can\'t be same.');
                    return $count > 0 ? false : true;
                });*/

                $validator = Validator::make($item, Student::rules($states_arr, $countries_arr), [
                    //'email.email_program_unique' => 'Email address and Program can not be same for more than one record'
                ], Student::attributeNames());

                if($request->duplicate_allow == 0 && !empty($item['email']) && !empty($item['program_id']) && in_array($item['email'].'-'.$item['program_id'], $unique_check)){
                    $get_unique_errors[$index]['email'] = 'Email address('.$item['email'].') and program ('.$program_id_name[$item['program_id']].') can\'t be same.';
                }

                $unique_check[$index] = $item['email'].'-'.$item['program_id'];

                if($validator->fails()){
                    $get_errors = $validator->errors()->toArray();
                    $errors[$index] = $get_errors;
                }

                $item['start_date'] = !empty($item['start_date']) ? Carbon::create($item['start_date'])->format('Y-m-d'):null;
                $item['end_date'] = !empty($item['end_date']) ? Carbon::create($item['end_date'])->format('Y-m-d'):null;
                $item['created_by'] = Auth::user()->id;
                $item['status'] = Student::STATUS_ACTIVE;
                $item['payment_amount'] = !empty($item['program_id']) ? $program_unite_price[$item['program_id']]:null;

                if($item['end_date'] == null){
                    $program_detail = DB::table('pas_program')->where('id', '=', $item['program_id'])->get()->first();
                    if($program_detail && !empty($program_detail->duration_type) && !empty($program_detail->duration_value)){
                        $item['end_date'] = date('Y-m-d', strtotime($item['start_date'] .' + '.intval($program_detail->duration_value).' '.$program_detail->duration_type));
                    }
                }

                if(!empty($item['email'])) {
                    // Check Leads already exists than Update Leads
                    $criteria = [
                        ['Email', 'equals', urlencode($item['email'])],
                        ['Program.id', 'equals', !empty($item['program_id']) ? $program_zid_name[$item['program_id']]:null],
                    ];

                    $lead = ZohoHelper::getInstance()->fetchCriteria('Leads', [], 1, 1, $criteria);

                    if (isset($lead['status']) && $lead['status'] == 'error') {
                        Log::channel('enrollment')->info('Validation Failed Zoho Check LEAD exists', $lead);
                        return response()->json(['status' => 'fail', 'errors' => $lead['message']]);
                    }

                    $pas_data[$index]['pas_student_email'] = [
                        'student_name' => $item['first_name'] . ' ' . $item['last_name'],
                        'email' => $item['email'],
                        'phone' => $item['phone'],
                        'program_name' => !empty($item['program_id']) ? $program_id_name[$item['program_id']] : null,

                        'partner_name' => User::getPartnerDetail('partner_name'),
                        'price_paid' => $item['price_paid'],
                        'payment_type' => $item['payment_type'],
                        'start_date' => date('m/d/Y', strtotime($item['start_date'])),
                        'end_date' => !empty($item['end_date']) ? date('m/d/Y', strtotime($item['end_date'])):null,
                        'street' => $item['street'],
                        'city' => $item['city'],
                        'state' => DB::table('pas_state')->where('id', '=',$item['state'])->value('iso2_code'),
                        'zip' => $item['zip'],
                        'country' => DB::table('pas_country')->where('id', '=', $item['country'])->value('country_name'),
                    ];

                    if (count($lead['data']) > 0 && isset($lead['data'][0]['id'])) {
                        $item['zoho_id'] = $lead['data'][0]['id'];

                        $pas_data[$index]['zoho_leads'] = Student::loadLeadsData($item, $lead, $program_zid_name, $program_id_name, $states, $countries);

                    } else {
                        // Check Deals already exists in our database combination of Email and Program
                        $check_schedule = DB::table('pas_schedule')
                            ->where('email', '=', $item['email'])
                            ->where('program_id', $item['program_id'])
                            ->where('partner_id', $item['partner_id'])
                            ->count('id');

                        if($check_schedule == 0){
                            // Check Deals already exists in ZOHO CRM combination of Email and Program
                            /*$criteria = [
                                ['Email', 'equals', $item['email']],
                                ['Program.id', 'equals', $program_zid_name[$item['program_id']]],
                            ];

                            $deal = ZohoHelper::getInstance()->fetchCriteria('Deals', [], 1, 1, $criteria);

                            if (isset($deal['status']) && $deal['status'] == 'error') {
                                Log::channel('enrollment')->info('Validation Failed Zoho Check DEALS exists', $deal);
                                return response()->json(['status' => 'fail', 'errors' => $deal['message']]);
                            }*/

                            $deal = Student::getDealWithCriteria($item['email'], $program_zid_name[$item['program_id']]);

                            if($deal['status'] == 'fail'){
                                Log::channel('enrollment')->info('Validation Failed Zoho Check DEALS exists', $deal);
                                return response()->json(['status' => 'fail', 'errors' => $deal['errors']]);
                            }

                            if (count($deal['data']) > 0 && isset($deal['data'][0]['id'])) {
                                $check_schedule = count($deal['data']);
                            }
                        }


                        if ($check_schedule > 0) {
                            $errors[$index]['email'][0] = 'Email address and Program can not be same for more than one record';
                        } else {
                            $pas_data[$index]['zoho_contact'] = Student::loadContactData($item, $states, $countries);
                            $pas_data[$index]['zoho_deals'] = Student::loadDealsData($item, $program_zid_name, $program_id_name, $states, $countries);
                            $programs_zoho_id = DB::table('pas_program')
                                ->where('id', '=', $item['program_id'])
                                ->value('zoho_id');

                            $state_code = DB::table('pas_state')
                                ->where('id', '=', $item['state'])
                                ->value('iso2_code');

                            $country_name = DB::table('pas_country')
                                ->where('id', '=', $item['country'])
                                ->value('country_name');

                            $pas_data[$index]['pas_schedule'] = [
                                'partner_id' => User::getPartnerDetail('id'),
                                'partner_zoho_id' => User::getPartnerDetail('zoho_id'),
                                'deal_name' => DB::raw('AES_ENCRYPT("' . $item['first_name'] . ' ' . $item['last_name'] . '", "' . $_ENV['AES_ENCRYPT_KEY'] . '")'),
                                'email' => DB::raw('AES_ENCRYPT("' . $item['email'] . '", "' . $_ENV['AES_ENCRYPT_KEY'] . '")'),
                                'phone' => !empty($item['phone']) ? DB::raw('AES_ENCRYPT("' . $item['phone'] . '", "' . $_ENV['AES_ENCRYPT_KEY'] . '")') : null,
                                'stage' => 'Enrollment Processed',
                                'payment_amount' => $item['payment_amount'],
                                'amount' => $item['price_paid'],
                                'start_date' => $item['start_date'],
                                'end_date' => $item['end_date'],
                                'street' => $item['street'],
                                'city' => $item['city'],
                                'state' => $state_code,
                                'zip' => $item['zip'],
                                'country' => $country_name,
                                'payment_type' => $item['payment_type'],
                                'program_id' => $item['program_id'],
                                'program_zoho_id' => $programs_zoho_id,
                                'created_at' => date('Y-m-d H:i:s')
                            ];

                            $pas_data[$index]['contact'] = [
                                'partner_id' => User::getPartnerDetail('id'),
                                'partner_zoho_id' => User::getPartnerDetail('zoho_id'),
                                'first_name' => DB::raw('AES_ENCRYPT("' . $item['first_name'] . '", "' . $_ENV['AES_ENCRYPT_KEY'] . '")'),
                                'last_name' => DB::raw('AES_ENCRYPT("' . $item['last_name'] . '", "' . $_ENV['AES_ENCRYPT_KEY'] . '")'),
                                'email' => DB::raw('AES_ENCRYPT("' . $item['email'] . '", "' . $_ENV['AES_ENCRYPT_KEY'] . '")'),
                                'phone' => !empty($item['phone']) ? DB::raw('AES_ENCRYPT("' . $item['phone'] . '", "' . $_ENV['AES_ENCRYPT_KEY'] . '")') : null,
                                'contact_role' => 'Student',
                                'mailing_city' => $item['city'],
                                'mailing_state' => $state_code,
                                'mailing_street' => $item['street'],
                                'mailing_zip' => $item['zip'],
                                'mailing_country' => $country_name,
                                'created_at' => date('Y-m-d H:i:s')
                            ];
                        }
                    }
                }

                $check_enrollment = DB::table('pas_student')->where(DB::raw('AES_DECRYPT(email, "' . $_ENV['AES_ENCRYPT_KEY'] . '")'), $item['email'])->where('program_id', $item['program_id'])->get()->first();
                if($check_enrollment){
                    $item['id'] = $check_enrollment->id;
                }

                // Data Encryption for Student Information
                $item['first_name'] = DB::raw('AES_ENCRYPT("'.$item['first_name'].'", "'.$_ENV['AES_ENCRYPT_KEY'].'")');
                $item['last_name'] = DB::raw('AES_ENCRYPT("'.$item['last_name'].'", "'.$_ENV['AES_ENCRYPT_KEY'].'")');
                $item['email'] = DB::raw('AES_ENCRYPT("'.$item['email'].'", "'.$_ENV['AES_ENCRYPT_KEY'].'")');
                $item['phone'] = DB::raw('AES_ENCRYPT("'.$item['phone'].'", "'.$_ENV['AES_ENCRYPT_KEY'].'")');
                $item['created_at'] = date('Y-m-d H:i:s');
                $item['created_by'] = Auth::user()->id;
                $pas_data[$index]['pas_student'] = $item;

            }

        }

        //dd($pas_data);

        foreach ($get_unique_errors as $err_index => $get_unique_error) {
            $errors[$err_index][array_key_first($get_unique_error)][0] = current($get_unique_error);
        }

        if(count($errors) > 0){
            Log::channel('enrollment')->info('Validation Failed', $errors);
            return response()->json(['status' => 'fail', 'errors' => $errors]);
        }

        Log::channel('enrollment')->info('Validate data END');

        // Activity Log store into database
        $data['action'] = 'create';
        $data['new_data'] = json_encode($pas_data);
        UserActivityHelper::getInstance()->save($request, $data);

        if(count($pas_data) > 0){
             try{
                foreach ($pas_data as $record) {
                    if(isset($record['pas_student']['id']) && $record['pas_student']['id'] > 0){
                        $id = $record['pas_student']['id'];
                        unset($record['pas_student']['id']);
                        DB::table('pas_student')
                            ->where('id', '=', $id)
                            ->update($record['pas_student']);
                        Log::channel('enrollment')->info('Record update into pas_student', $record['pas_student']);
                    }else{
                        DB::table('pas_student')->insert($record['pas_student']);
                        Log::channel('enrollment')->info('Record inserted into pas_student', $record['pas_student']);
                    }

                    if(isset($record['zoho_leads']) && count($record['zoho_leads']) > 0){
                        Log::channel('enrollment')->info('ZOHO Leads data to be updated', $record['zoho_leads']);
                        $lead_owner_email = $record['zoho_leads']['Owner_Email'];
                        unset($record['zoho_leads']['Owner_Email']);
                        $leads_response = ZohoHelper::getInstance()->updateRecord([$record['zoho_leads']], 'Leads');
                        if(isset($leads_response['status']) && $leads_response['status'] == 'error'){
                            Log::channel('enrollment')->info('ZOHO Leads record update failed', $leads_response);
                            return response()->json(array("status"=>"fail", "zoho_errors"=> $leads_response['message']));
                        }
                        Log::channel('enrollment')->info('ZOHO Leads updated: ', $leads_response);

                        $note_data['Note_Title'] = 'Leads Update from PAS';
                        $note_content = '';
                        foreach ($record['zoho_leads'] as $key => $zoho_lead) {
                            if(!is_array($zoho_lead) && $key != 'id' && $key != 'School_Id' && $key != 'Program_Id'){
                                $note_content .= str_replace('_', ' ', $key).': '.$zoho_lead.''.PHP_EOL;
                            }
                        }

                        $note_data['Note_Content'] = $note_content;
                        $note_data['Parent_Id'] = $record['zoho_leads']['id'];
                        $note_data['se_module'] = 'Leads';
                        $notes_response = ZohoHelper::getInstance()->addRecord([$note_data], 'Notes');
                        if(isset($notes_response['status']) && $notes_response['status'] == 'error'){
                            Log::channel('enrollment')->info('ZOHO Leads Notes record create failed', $leads_response);
                            return response()->json(array("status"=>"fail", "zoho_errors"=> $notes_response['message']));
                        }

                        // Send Email Notification to logged in and Environment variable Users
                        (new Student())->sendStudentLeadConvertToEnrollmentEmail($record['pas_student_email'], $lead_owner_email);
                    }

                    if(isset($record['zoho_contact']) && count($record['zoho_contact']) > 0){
                        $criteria = [
                            ['Email', 'equals', $record['zoho_contact']['Email']],
                        ];

                        $contact = ZohoHelper::getInstance()->fetchCriteria('Contacts', ['Owner'], 1, 1, $criteria);
                        //dd($contact);
                        if(isset($contact['status']) && $contact['status'] == 'error'){
                            Log::channel('enrollment')->info('ZOHO Contact fetch failed', $contact);
                            return response()->json(['status' => 'fail', 'errors' => $contact['message']]);
                        }

                        if(count($contact['data']) > 0 && isset($contact['data'][0]['id'])) {
                            $record['zoho_deals']['Contact_Name']['id'] = $contact['data'][0]['id'];
                            Log::channel('enrollment')->info('ZOHO Contact exists so contact will not be created');
                        }else {
                            Log::channel('enrollment')->info('ZOHO Contact data to be inserted', $record['zoho_contact']);
                            $contact_response = ZohoHelper::getInstance()->addRecord([$record['zoho_contact']], 'Contacts');
                            if(isset($contact_response['status']) && $contact_response['status'] == 'error'){
                                Log::channel('enrollment')->info('ZOHO Contact create failed', $contact_response);
                                return response()->json(array("status"=>"fail", "zoho_errors"=> $contact_response['message']));
                            }
                            Log::channel('enrollment')->info('ZOHO Contact created: ', $contact_response);

                            $record['zoho_deals']['Contact_Name']['id'] = $contact_response[0]['details']['id'];
                        }

                        $deals_response = ZohoHelper::getInstance()->addRecord([$record['zoho_deals']], 'Deals');
                        if(isset($deals_response['status']) && $deals_response['status'] == 'error'){
                            Log::channel('enrollment')->info('ZOHO Deals create failed', $deals_response);
                            return response()->json(array("status"=> "fail", "zoho_errors"=> $deals_response['message']));
                        }
                        $record['pas_schedule']['zoho_id'] = $deals_response[0]['details']['id'];
                        $record['pas_schedule']['contact_zoho_id'] = $record['zoho_deals']['Contact_Name']['id'];
                        DB::table('pas_schedule')->insert($record['pas_schedule']);

                        if(isset($pas_data[$index]['contact'])){
                            $contact_count = DB::table('pas_contact')
                                ->where('zoho_id', '=', $record['zoho_deals']['Contact_Name']['id'])
                                ->count('id');
                            if(empty($contact_count)){
                                $pas_data[$index]['contact']['zoho_id'] = $record['zoho_deals']['Contact_Name']['id'];
                                DB::table('pas_contact')->insert($pas_data[$index]['contact']);
                            }
                        }
                        // Send Email Notification to logged in and Environment variable Users
                        (new Student())->sendStudentEnrollmentEmailNew($record['pas_student_email']);

                        //dd($deals_response);
                    }
                }
                Log::channel('enrollment')->info('CREATE NEW ENROLLMENT END');
                return response()->json(['status' => 'success', 'msg' => 'Data added successfully.']);
             }catch (Exception $e){
                 Log::channel('enrollment')->info('CREATE NEW ENROLLMENT ERROR', $e);
                 return response()->json(array("status"=>"fail", "errors"=> $e->getMessage()));
             }

        }
    }

    public function search(Request $request) {
        if(!UserAccess::hasAccess(UserAccess::STUDENT_MANAGEMENT_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $result = $this->getSearchData($request);
        //dd($result->groupBy('email'));
        $result_group = $result->groupBy('email');
        //dd($result_group);
        /*foreach($result as $key => $val){
            $result[$key]->id = pas_encrypt($val->id);
            $result[$key]->parent = str_replace(['@', '.'], '-', $val->email);
            $result[$key]->payment_type = $val->payment_type;
        }*/
        $sort_column = $request->sort_column;
        $sort_order = $request->sort_order;
        $column_setting = ListingSetting::getStudentEnrollmentDefaultListing();
        $total = $this->getSearchData($request, true);
        return view('student._view', compact('result_group','column_setting','total', 'sort_column', 'sort_order'));
        //return response()->json(['total_record' => $this->getSearchData($request, '', true), 'result' => $    ]);
    }

    public function loadMore(Request $request) {
        if(!UserAccess::hasAccess(UserAccess::STUDENT_MANAGEMENT_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $result = $this->getSearchData($request);
        if($result) {
            foreach ($result as $key => $val) {
                $result[$key]->id = pas_encrypt($val->id);
                //$result[$key]->status = Student::getStatus($val->status);
                $result[$key]->parent = str_replace(['@', '.'], '-', $val->email);
                $result[$key]->payment_type = $val->payment_type;
            }
            //return response()->json($result);
        }
        $column_setting = ListingSetting::getStudentEnrollmentDefaultListing();
        $id = $request->id;
        return view('student._loadview', compact('result','column_setting','id'));
        //return response()->json([]);
    }

    public function delete(){
        $ids = request('id');
        $ids_arr = @explode(',', $ids);

        if(count($ids_arr) > 0){
            $ids_arr = array_filter(array_map('pas_decrypt', $ids_arr));
            DB::table('pas_student')->whereIn('id', $ids_arr)->delete();
        }
        return response()->json(["status"=>"success"]);
    }


    public function exportExcel(Request $request) {
        if(!UserAccess::hasAccess(UserAccess::STUDENT_MANAGEMENT_ACCESS, 'download')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'First Name');
        $sheet->setCellValue('B1', 'Last Name');
        $sheet->setCellValue('C1', 'Email');
        $sheet->setCellValue('D1', 'Program');
        $sheet->setCellValue('E1', 'Payment Amount');
        $sheet->setCellValue('F1', 'Payment Type');
        $sheet->setCellValue('G1', 'Start Date');
        $sheet->setCellValue('H1', 'End Date');
        $sheet->setCellValue('I1', 'Phone');
        $sheet->setCellValue('J1', 'Street');
        $sheet->setCellValue('K1', 'City');
        $sheet->setCellValue('L1', 'State');
        $sheet->setCellValue('M1', 'Zip');
        $sheet->setCellValue('N1', 'Country');

        $result = $this->getSearchData($request);

        $rows = 2;
        foreach($result as $val){
            $sheet->setCellValue('A' . $rows, $val->first_name);
            $sheet->setCellValue('B' . $rows, $val->last_name);
            $sheet->setCellValue('C' . $rows, $val->email);
            $sheet->setCellValue('D' . $rows, $val->program_name);
            $sheet->setCellValue('E' . $rows, $val->payment_amount);
            $sheet->setCellValue('F' . $rows, $val->payment_type);
            $sheet->setCellValue('G' . $rows, $val->start_date);
            $sheet->setCellValue('H' . $rows, $val->end_date);
            $sheet->setCellValue('I' . $rows, $val->phone);
            $sheet->setCellValue('J' . $rows, $val->street);
            $sheet->setCellValue('K' . $rows, $val->city);
            $sheet->setCellValue('L' . $rows, $val->state);
            $sheet->setCellValue('M' . $rows, $val->zip);
            $sheet->setCellValue('N' . $rows, $val->country);
            $rows++;
        }

        $filename = "student_lists.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save("export/".$filename);

        ob_end_clean(); // this is solution
        header('Content-Description: File Transfer');
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"" . basename($filename) . "\"");
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        readfile("export/".$filename);
        unlink("export/".$filename);
    }

    public function exportPdf(Request $request){
        if(!UserAccess::hasAccess(UserAccess::STUDENT_MANAGEMENT_ACCESS, 'download')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
            $result = $this->getSearchData($request);

            $hd = public_path('images/logo.png');
            $wt = public_path('images/bg.png');
            $str = '';
            $str .= '<div style="border:2px solid #666; padding:10px; font-family: arial, sans-serif;">';
            $str .= '<div style="text-align:center"><img src="'.$hd.'" style="width:600px" alt=""/></div>';
            $str .= '<div style="position: relative;">';
            //$str .= '<div style="text-align:center;"><img src="'.$wt.'" style="width:500px" alt=""/></div>';
            $str .= '<div style="position:absolute;top:0px;width:100%">';
            $str .= '<h2 style="padding:10px; font-family: arial, sans-serif;font-size: 16px;">Student Enrollment</h2>';
            $str .= '<table style="width:100%;margin:0 auto;border-collapse: collapse;border:1px solid #333;">';
            $str .= '<tr>';
            $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:8%;font-weight:bold;text-align:left">First Name</th>';
            $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:8%;font-weight:bold;text-align:left">Last Name</th>';
            $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:8%;font-weight:bold;text-align:left">Email</th>';
            $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:8%;font-weight:bold;text-align:left">Program</th>';
            $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:8%;font-weight:bold;text-align:left">Payment Amount</th>';
            $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:8%;font-weight:bold;text-align:left">Payment Type</th>';
            $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:8%;font-weight:bold;text-align:left">Start Date</th>';
            $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:8%;font-weight:bold;text-align:left">End Date</th>';
            $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:8%;font-weight:bold;text-align:left">Phone</th>';
            $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:8%;font-weight:bold;text-align:left">Street</th>';
            $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:8%;font-weight:bold;text-align:left">City</th>';
            $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:8%;font-weight:bold;text-align:left">State</th>';
            $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:8%;font-weight:bold;text-align:left">Zip</th>';
            $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-bottom:1px solid #333;width:8%;font-weight:bold;text-align:left">Country</th>';
            $str .= '</tr>';


        if(count($result) > 0){
            foreach($result as $val){
                $str .= '<tr>';
                $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->first_name.'</td>';
                $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->last_name.'</td>';
                $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->email.'</td>';
                $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->program_name.'</td>';
                $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->payment_amount.'</td>';
                $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->payment_type.'</td>';
                $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->start_date.'</td>';
                $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->end_date.'</td>';
                $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->phone.'</td>';
                $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->street.'</td>';
                $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->city.'</td>';
                $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->state.'</td>';
                $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->zip.'</td>';
                $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->country.'</td>';
                $str .= '</tr>';

            }
        }else{
                $str .= '<tr>';
                $str .= '<td colspan="16" style="text-align:center;">No Record Found.</td>';
                $str .= '</tr>';
        }
        $str .= '</table>';
        $str .= '</div>';
        $str .= '</div>';
        $str .= '</div>';
        //echo $str;die;
        $mpdf = new mPDF([
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 5,
                'margin_right' => 5,
                'margin_top' => 5,
                'margin_bottom' => 5
            ]);

        $mpdf->SetWatermarkImage($wt,0.4,'',array(40,50));
        $mpdf->showWatermarkImage = true;
        $mpdf->WriteHTML($str);
        return $mpdf->Output("Student_Enrollment.pdf", 'D');
    }

    /**
     * @param Request $request
     * @param bool $totalCount
     * @return \Illuminate\Support\Collection|int
     */
    private function getSearchData(Request $request, $totalCount = false){
        $sub_query = '';
        if(User::getPartnerDetail('id')){
            $sub_query = ' AND partner_id = '.User::getPartnerDetail('id').'';
        }

        $query = DB::table('pas_student AS s')->select('s.id', DB::raw('AES_DECRYPT(first_name, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS first_name'), DB::raw('AES_DECRYPT(last_name, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS last_name'), DB::raw('AES_DECRYPT(email, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS email'), DB::raw('AES_DECRYPT(phone, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS phone'), 'street', 'city', Db::raw('state_name AS state'), 'zip', Db::raw('country_name AS country'), 'payment_type', Db::raw('DATE_FORMAT(start_date, "'.Utility::DEFAULT_DATE_FORMAT_MYSQL.'") AS start_date'), Db::raw('DATE_FORMAT(end_date, "'.Utility::DEFAULT_DATE_FORMAT_MYSQL.'") AS end_date'), Db::raw('DATE_FORMAT(complete_date, "'.Utility::DEFAULT_DATE_FORMAT_MYSQL.'") AS complete_date'), Db::raw('(SELECT COUNT(id) FROM pas_student WHERE email = s.email AND id != s.id '.$sub_query.') AS total'), 'pas_program.name AS program_name', 'pas_program.zoho_id AS program_zoho_id', 'payment_amount', 'price_paid', 'attachment', 'partner_id')
            ->leftJoin('pas_program', function($join){
                $join->on('program_id', '=', 'pas_program.id');
            })->leftJoin('pas_state', function($join){
                $join->on('state', '=', 'pas_state.id');
            })->leftJoin('pas_country', function($join){
                $join->on('country', '=', 'pas_country.id');
            });

        if (isset($request->q)){
            $query->where(function ($query) use ($request) {
                $query->orwhere(DB::raw('CAST(AES_DECRYPT(s.first_name, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS CHAR)'), 'like', '%'.$request->q.'%')
                    ->orwhere(DB::raw('CAST(AES_DECRYPT(s.last_name, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS CHAR)'), 'like', '%'.$request->q.'%')
                    ->orwhere(DB::raw('CAST(AES_DECRYPT(s.email, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS CHAR)'), 'like', '%'.$request->q.'%')
                    ->orwhere(DB::raw('CAST(AES_DECRYPT(s.phone, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS CHAR)'), 'like', '%'.$request->q.'%')
                    ->orwhere('name', 'like', '%'.$request->q.'%')
                    ->orwhere('payment_type', 'like', '%'.$request->q.'%')
                    ->orwhere('payment_amount', 'like', '%'.$request->q.'%')
                    ->orwhere('price_paid', 'like', '%'.$request->q.'%')
                    ->orwhere('street', 'like', '%'.$request->q.'%')
                    ->orwhere('city', 'like', '%'.$request->q.'%')
                    ->orwhere('zip', 'like', '%'.$request->q.'%')
                    ->orwhere('country_name', 'like', '%'.$request->q.'%');
                    $query->orwhere(Db::raw('(DATE_FORMAT(start_date, "%m/%d/%Y"))'), 'like', "%".$request->q."%");
                    $query->orwhere(Db::raw('(DATE_FORMAT(end_date, "%m/%d/%Y"))'), 'like', "%".$request->q."%");
            });
        }

        /*if (!empty($request->id))
            $query->where('s.id', '!=', pas_decrypt($request->id));*/
        if (!empty($request->email))
            $query->where(DB::raw('CAST(AES_DECRYPT(s.email, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS CHAR)'), '=', $request->email);
        if (!empty($request->fname))
            $query->where(DB::raw('CAST(AES_DECRYPT(s.first_name, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS CHAR)'), 'like', "%".$request->fname."%");
        if (!empty($request->lname))
            $query->where(DB::raw('CAST(AES_DECRYPT(s.last_name, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS CHAR)'), 'like', '%'.$request->lname.'%');
        if (!empty($request->program))
            $query->where('name', 'like', '%'.$request->program.'%');
        if (!empty($request->sdate))
            $query->where('s.start_date', '=', Carbon::create($request->sdate)->format('Y-m-d'));
        if (!empty($request->type))
            $query->where('payment_type', '=', $request->type);

        if(User::getPartnerDetail('id')){
            $query->where('partner_id', '=', User::getPartnerDetail('id'));
        }

        if($totalCount){
            return $query->count();
        }
        /*if(!empty($groupBy)){
            $query->groupBy('s.email');
        }*/
        $query->orderBy($request->sort_column, $request->sort_order);
        //$query->orderBy('s.start_date', 'DESC');
        return $query->get();
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    /*public function getPrograms(Request $request){
        //if(!UserAccess::hasAccess(UserAccess::STUDENT_MANAGEMENT_ACCESS, 'view')){
           // return view(Utility::ERROR_PAGE_TEMPLATE);
        //}
        if(!empty($request->query)){
            $programs = Program::select('id', 'name')
                ->where([
                    ['status', '=', 'Active'],
                    ['name', 'like', request('query').'%'],
                ])
                ->orderBy('name', 'ASC')
                ->get();
            return response()->json($programs);
        }
        return response()->json([]);

    }*/

    public function templatedownload() {
        if(!UserAccess::hasAccess(UserAccess::STUDENT_MANAGEMENT_ACCESS, 'add')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'First Name');
        $sheet->setCellValue('B1', 'Last Name');
        $sheet->setCellValue('C1', 'Email');
        $sheet->setCellValue('D1', 'Program');
        $sheet->setCellValue('E1', 'Price Paid');
        $sheet->setCellValue('F1', 'Payment Type');
        $sheet->setCellValue('G1', 'Start Date');
        $sheet->setCellValue('H1', 'End Date');
        $sheet->setCellValue('I1', 'Phone');
        $sheet->setCellValue('J1', 'Street');
        $sheet->setCellValue('K1', 'City');
        $sheet->setCellValue('L1', 'State');
        $sheet->setCellValue('M1', 'ZIP');
        $sheet->setCellValue('N1', 'Country');

        $states = State::where('status', '=', 1)->get();
        $spreadsheet->getActiveSheet();
        $spreadsheet->createSheet();
        $j=1;
        foreach($states as $state){
            $spreadsheet->setActiveSheetIndex(1) ->setCellValue('A'.$j, $state->state_name);
        $j++;}
        $spreadsheet->getActiveSheet()->setTitle('States');

        $countries = Country::where('status', '=', 1)->get();
        $spreadsheet->getActiveSheet();
        $spreadsheet->createSheet();
        $l=1;
        foreach($countries as $country){
            $spreadsheet->setActiveSheetIndex(2) ->setCellValue('A'.$l, $country->country_name);
        $l++;}
        $spreadsheet->getActiveSheet()->setTitle('Countries');

        //for program dropdown
        $programs = Program::where('status', '=', 'Active')->orderBy('name', 'ASC')->get();
        $spreadsheet->getActiveSheet();
        $spreadsheet->createSheet();
        $p=1;
        foreach($programs as $program){
            $spreadsheet->setActiveSheetIndex(3) ->setCellValue('A'.$p, $program['name']);
        $p++;}
        $spreadsheet->getActiveSheet()->setTitle('Program');
        $spreadsheet->setActiveSheetIndex(0);

        //for payment type dropdown
        $spreadsheet->getActiveSheet();
        $spreadsheet->createSheet();
        $b=1;
        foreach(Student::getPaymentType() as $id => $payment_type){
            $spreadsheet->setActiveSheetIndex(4) ->setCellValue('A'.$b, $payment_type['actual_value']);
        $b++;}
        $spreadsheet->getActiveSheet()->setTitle('Payment Type');
        $spreadsheet->setActiveSheetIndex(0);



        for($n=2;$n<11;$n++){
            $validation = $spreadsheet->getActiveSheet(0)->getCell('L'.$n)->getDataValidation();
            $validation->setType( \PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST );
            $validation->setErrorStyle( \PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION );
            $validation->setShowDropDown(true);
            $validation->setFormula1('\'States\'!$A$1:$A$'.$j);

            $validation = $spreadsheet->getActiveSheet(0)->getCell('N'.$n)->getDataValidation();
            $validation->setType( \PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST );
            $validation->setErrorStyle( \PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION );
            $validation->setShowDropDown(true);
            $validation->setFormula1('\'Countries\'!$A$1:$A$'.$l);

            $validation = $spreadsheet->getActiveSheet(0)->getCell('D'.$n)->getDataValidation();
            $validation->setType( \PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST );
            $validation->setErrorStyle( \PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION );
            $validation->setShowDropDown(true);
            $validation->setFormula1('\'Program\'!$A$1:$A$'.$p);

            $validation = $spreadsheet->getActiveSheet(0)->getCell('F'.$n)->getDataValidation();
            $validation->setType( \PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST );
            $validation->setErrorStyle( \PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION );
            $validation->setShowDropDown(true);
            $validation->setFormula1('\'Payment Type\'!$A$1:$A$'.$b);
        }


        $cell_st =[
            'font' =>['bold' => true]
        ];
        $sheet->getStyle('A1')->applyFromArray($cell_st);
        $sheet->getStyle('B1')->applyFromArray($cell_st);
        $sheet->getStyle('C1')->applyFromArray($cell_st);
        $sheet->getStyle('D1')->applyFromArray($cell_st);
        $sheet->getStyle('E1')->applyFromArray($cell_st);
        $sheet->getStyle('F1')->applyFromArray($cell_st);
        $sheet->getStyle('G1')->applyFromArray($cell_st);
        $sheet->getStyle('I1')->applyFromArray($cell_st);
        $sheet->getStyle('J1')->applyFromArray($cell_st);
        $sheet->getStyle('K1')->applyFromArray($cell_st);
        $sheet->getStyle('L1')->applyFromArray($cell_st);
        $sheet->getStyle('M1')->applyFromArray($cell_st);
        $sheet->getStyle('N1')->applyFromArray($cell_st);

        $filename = "sample_download_student.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save("export/".$filename);

        header('Content-Description: File Transfer');
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"" . basename($filename) . "\"");
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        readfile("export/".$filename);
        unlink("export/".$filename);
    }

    public function detail(Request $request){
        if(!UserAccess::hasAccess(UserAccess::STUDENT_MANAGEMENT_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        //if(!empty(pas_decrypt($request->id)) && is_numeric(pas_decrypt($request->id))){

            $student = DB::table('pas_student AS s')->select('s.id', DB::raw('AES_DECRYPT(first_name, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS first_name'), DB::raw('AES_DECRYPT(last_name, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS last_name'), DB::raw('AES_DECRYPT(email, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS email'), DB::raw('AES_DECRYPT(phone, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS phone'), 'street', 'city', Db::raw('state_name AS state'), 'zip', Db::raw('country_name AS country'), 's.status', 'payment_type', Db::raw('DATE_FORMAT(start_date, "%d/%m/%y") AS start_date'), Db::raw('DATE_FORMAT(end_date, "%d/%m/%y") AS end_date'), Db::raw('DATE_FORMAT(complete_date, "%d/%m/%y") AS complete_date'), 'pas_program.name AS program_name', 'payment_amount')
                ->leftJoin('pas_program', function($join){
                    $join->on('program_id', '=', 'pas_program.id');
                })->leftJoin('pas_state', function($join){
                    $join->on('state', '=', 'pas_state.id');
                })->leftJoin('pas_country', function($join){
                    $join->on('country', '=', 'pas_country.id');
                })
                ->where('s.id', '=', pas_decrypt($request->id))->get()->first();
        //}
        return view('student.detail', compact('student'));
    }

    public function mapMyStudent(Request $request){
        if(!UserAccess::hasAccess(UserAccess::MAP_MY_STUDENTS_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }

        $student_locations = DB::table('pas_schedule')->where([
            ['partner_id', '=', User::getPartnerDetail('id')],
        ])->select(['iso2_code', 'state_name'])->join('pas_state', 'state', '=', 'pas_state.iso2_code')
            ->get()->toArray();

        $student_by_locations = [];

        foreach ($student_locations as $location) {
            $student_by_locations[$location->iso2_code][] = $location->state_name;
        }

        return view('student.map-my-student', compact('student_by_locations'));
    }

    public function setPartner(Request $request){
        if(User::isPartner() || User::isMyUser()){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }

        if(!User::isPartner() && !empty($request->partner)){
            $partner = Partner::
                select(['id', \Illuminate\Support\Facades\DB::raw('CAST(zoho_id AS CHAR) AS zoho_id'), 'canvas_sub_account_id', 'partner_name', 'contact_name', 'hosted_site', 'title', 'phone', 'email', 'pi_phone', 'pi_email', 'department' , 'wia', 'mycaa', 'street','city', 'state', 'zip_code', 'price_book_id', 'price_book_zoho_id', 'logo', 'status'])
                ->where('zoho_id', '=', $request->partner)
                ->get()->first()->toArray();
            Session::put('partner_detail', $partner);
        }
        return response()->json(['status' => true]);
    }
}