<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Program;
use App\Models\State;
use App\Models\Student;
use App\Models\User;
use App\UserActivityHelper;
use App\ZohoHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Exception;
use Session;
use Config;
use Lang;
use Cookie;

require base_path("vendor/autoload.php");

class StudentimportController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function importFile(Request $request)
    {
        $filename = $request->filename;

        if(!$filename){
            return response()->json(['status' => 'fail', 'msg' => 'Please select a file.']);
        }

        $size = $request->file('filename')->getSize();
        $ext = $filename->getClientOriginalExtension();

        if(strtolower($ext) != 'xlsx' && strtolower($ext) != 'xls'){
            return response()->json(['status' => 'fail', 'msg' => 'Please select valid file.']);
        }

        $newfname = "student_".time().".".$ext;

        $filename->move(public_path('uploads'), $newfname);
        $fxls = public_path('uploads').'/'.$newfname;
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fxls);
        $xls_data = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        if(strcasecmp(trim($xls_data[1]['A']), 'First Name') != 0 || strcasecmp(trim($xls_data[1]['B']), 'Last Name') != 0 || strcasecmp(trim($xls_data[1]['C']), 'Email') != 0 || strcasecmp(trim($xls_data[1]['D']), 'Program') != 0 || strcasecmp(trim($xls_data[1]['E']), 'Price Paid') != 0 || strcasecmp(trim($xls_data[1]['F']), 'Payment Type') != 0 || strcasecmp(trim($xls_data[1]['G']), 'Start Date') != 0 || strcasecmp(trim($xls_data[1]['H']), 'End Date') != 0 || strcasecmp(trim($xls_data[1]['I']), 'Phone') != 0 || strcasecmp(trim($xls_data[1]['J']), 'Street') != 0 || strcasecmp(trim($xls_data[1]['K']), 'City') != 0 || strcasecmp(trim($xls_data[1]['L']), 'State') != 0 || strcasecmp(trim($xls_data[1]['M']), 'Zip') != 0 || strcasecmp(trim($xls_data[1]['N']), 'Country') != 0){
            return response()->json(['status' => 'fail', 'msg' => 'Please check the file column is missing.']);
        }

        Log::channel('enrollment')->info('Excel: CREATE NEW ENROLLMENT START');

        //$students = [];
        $pas_data = [];
        $unique_check = [];
        $errors = [];
        $warnings = [];

        $start_time = time();
        $nr = count($xls_data);

        $programs = Program::select('id','zoho_id', DB::raw('LOWER(name) AS name'), 'retail_wholesale')->get()->all();
        $program_arr = array_column($programs, 'id', 'name');
        $program_zid_name = array_column($programs, 'zoho_id', 'id');
        $program_id_name = array_column($programs, 'name', 'id');
        $program_wholesale_price = array_column($programs, 'retail_wholesale', 'id');
        $program_unite_price = array_column($programs, 'unite_price', 'id');

        $all_states = State::select('id',DB::raw('LOWER(state_name) AS state_name'))->get()->all();
        $states = array_column($all_states, 'state_name', 'id');
        $states_arr = array_column($all_states, 'id', 'state_name');

        $all_countries = Country::select('id',DB::raw('LOWER(country_name) AS country_name'))->get()->all();
        $countries = array_column($all_countries, 'country_name', 'id');
        $countries_arr = array_column($all_countries, 'id', 'country_name');

        Log::channel('enrollment')->info('Excel: Validate data START');
            for($i = 2; $i <= $nr;) {
                if(empty($xls_data[$i]['A']) && empty($xls_data[$i]['B']) && empty($xls_data[$i]['C']) && empty($xls_data[$i]['D']) && empty($xls_data[$i]['E']) && empty($xls_data[$i]['F']) && empty($xls_data[$i]['G']) && empty($xls_data[$i]['H']) && empty($xls_data[$i]['I']) && empty($xls_data[$i]['J']) && empty($xls_data[$i]['K']) && empty($xls_data[$i]['L']) && empty($xls_data[$i]['M']) && empty($xls_data[$i]['N']) ) {
                   break;
                }

                $xls_data[$i]['D'] = strtolower($xls_data[$i]['D']);
                $xls_data[$i]['L'] = strtolower($xls_data[$i]['L']);
                $xls_data[$i]['N'] = strtolower($xls_data[$i]['N']);

                $start_date = !empty(trim($xls_data[$i]['G'])) ? date('Y-m-d', strtotime(trim($xls_data[$i]['G']))):null;
                $end_date = !empty(trim($xls_data[$i]['H'])) ? date('Y-m-d', strtotime(trim($xls_data[$i]['H']))):null;

                //dd([trim($xls_data[$i]['E']), $program_unite_price]);
                $student_data['partner_id'] = User::getPartnerDetail('id');
                $student_data['first_name'] = trim($xls_data[$i]['A']);
                $student_data['last_name'] = trim($xls_data[$i]['B']);
                $student_data['email'] = trim($xls_data[$i]['C']);
                $student_data['program_id'] = isset($program_arr[$xls_data[$i]['D']]) ? $program_arr[$xls_data[$i]['D']]: 0;
                $student_data['payment_type'] = strtolower(trim($xls_data[$i]['F']));
                $student_data['payment_amount'] = isset($program_unite_price[$student_data['program_id']]) ? $program_unite_price[$student_data['program_id']]:null;
                $student_data['price_paid'] = trim($xls_data[$i]['E']);
                $student_data['start_date'] = trim($xls_data[$i]['G']);
                $student_data['end_date'] = !empty($xls_data[$i]['H']) ? trim($xls_data[$i]['H']):null;
                $student_data['phone'] = !empty($xls_data[$i]['I']) ? trim($xls_data[$i]['I']):null;
                $student_data['street'] = !empty($xls_data[$i]['J']) ? trim($xls_data[$i]['J']): null;
                $student_data['city'] = !empty($xls_data[$i]['K']) ? trim($xls_data[$i]['K']): null;
                $student_data['state'] = isset($states_arr[trim($xls_data[$i]['L'])]) ? $states_arr[trim($xls_data[$i]['L'])]:0;
                $student_data['zip'] = !empty($xls_data[$i]['M']) ? trim($xls_data[$i]['M']):null;
                $student_data['country'] = isset($countries_arr[trim($xls_data[$i]['N'])]) ? $countries_arr[trim($xls_data[$i]['N'])]:0;
                $student_data['created_by'] = Auth::user()->id;


                /*Validator::extend('email_program_unique', function($attribute, $value, $parameters, $validator) use($student_data) {
                    $count = DB::table('pas_student')->where(DB::raw('AES_DECRYPT(email, "'.$_ENV['AES_ENCRYPT_KEY'].'")'), $student_data['email'])->where('program_id', $student_data['program_id'])->count();
                    return $count > 0 ? false:true;
                });*/

                $student_data['start_date'] = date('m/d/Y', strtotime($student_data['start_date']));
                if(!empty($student_data['end_date'])){
                    $student_data['end_date'] = date('m/d/Y', strtotime($student_data['end_date']));
                }

                $validator = Validator::make($student_data, Student::rules($states_arr, $countries_arr), [
                    'email.email_program_unique' => 'Email address('.$student_data['email'].') and program can\'t be same.'
                ], Student::attributeNames());

                // Validate combination of email and program unique
                if(!empty($student_data['email']) && !empty($student_data['program_id']) && in_array($student_data['email'].'-'.$program_id_name[$student_data['program_id']], $unique_check)){
                    array_push($errors[$i]['email'], 'Email address and Program can not be same for more than one record');
                }

                $unique_check[$i] = $student_data['email'].'-'.$xls_data[$i]['D'];

                if($validator->fails()){
                    $get_errors = $validator->errors()->toArray();
                    $errors[$i] = $get_errors;
                } else {
                    $student_data['start_date'] = $start_date;
                    //$student_data['end_date'] = $end_date;

                    //$end_date = $student_data['end_date'];
                    if($end_date == null){
                        $program_detail = DB::table('pas_program')->where('id', '=', $student_data['program_id'])->get()->first();
                        if($program_detail && !empty($program_detail->duration_type) && !empty($program_detail->duration_value)){
                            $end_date = date('Y-m-d', strtotime($student_data['start_date'] .' + '.intval($program_detail->duration_value).' '.$program_detail->duration_type));
                        }
                    }
                    $student_data['end_date'] = $end_date;

                    if(!empty($student_data['email'])){
                        // Check Leads already exists than Update Leads
                        $criteria = [
                            ['Email', 'equals', urlencode($student_data['email'])],
                            ['Program.id', 'equals', !empty($student_data['program_id']) ? $program_zid_name[$student_data['program_id']]:null],
                        ];

                        $lead = ZohoHelper::getInstance()->fetchCriteria('Leads', [], 1, 1, $criteria);

                        if(isset($lead['status']) && $lead['status'] == 'error'){
                            Log::channel('enrollment')->info('Excel: Validation Failed Zoho Check LEAD exists', $lead);
                            return response()->json(['status' => 'fail', 'errors' => $lead['message']]);
                        }

                        $pas_data[$i]['pas_student_email'] = [
                            'student_name' => $student_data['first_name'] . ' ' . $student_data['last_name'],
                            'email' => $student_data['email'],
                            'phone' => $student_data['phone'],
                            'program_name' => !empty($student_data['program_id']) ? $program_id_name[$student_data['program_id']] : null,
                            'partner_name' => User::getPartnerDetail('partner_name'),
                            'price_paid' => $student_data['price_paid'],
                            'payment_type' => $student_data['payment_type'],
                            'start_date' => date('m/d/Y', strtotime($student_data['start_date'])),
                            'end_date' => !empty($student_data['end_date']) ? date('m/d/Y', strtotime($student_data['end_date'])):null,
                            'street' => $student_data['street'],
                            'city' => $student_data['city'],
                            'state' => DB::table('pas_state')->where('id', '=',$student_data['state'])->value('iso2_code'),
                            'zip' => $student_data['zip'],
                            'country' => DB::table('pas_country')->where('id', '=', $student_data['country'])->value('country_name'),
                        ];

                        if(count($lead['data']) > 0 && isset($lead['data'][0]['id'])){
                            $pas_data[$i]['zoho_leads'] = Student::loadLeadsData($student_data, $lead, $program_zid_name, $program_id_name, $states, $countries);
                        }
                        else {
                            $check_schedule = DB::table('pas_schedule')
                                ->where('email', '=', $student_data['email'])
                                ->where('program_id', $student_data['program_id'])
                                ->where('partner_id', $student_data['partner_id'])
                                ->count('id');

                            if($check_schedule == 0){
                                // Check Deals already exists in ZOHO CRM combination of Email and Program
                                /*$criteria = [
                                    ['Email', 'equals', $student_data['email']],
                                    ['Program.id', 'equals', $program_zid_name[$student_data['program_id']]],
                                ];

                                $deal = ZohoHelper::getInstance()->fetchCriteria('Deals', [], 1, 1, $criteria);

                                if (isset($deal['status']) && $deal['status'] == 'error') {
                                    Log::channel('enrollment')->info('Validation Failed Zoho Check DEALS exists', $deal);
                                    return response()->json(['status' => 'fail', 'errors' => $deal['message']]);
                                }*/

                                $deal = Student::getDealWithCriteria($student_data['email'], $program_zid_name[$student_data['program_id']]);

                                if (count($deal['data']) > 0 && isset($deal['data'][0]['id'])) {
                                    $check_schedule = count($deal['data']);
                                }
                            }

                            if($check_schedule > 0){
                                $errors[$i]['email'][0] = 'Email address and Program can not be same for more than one record';
                            }else {
                                $wholesale_price = isset($program_wholesale_price[$student_data['program_id']]) ? $program_wholesale_price[$student_data['program_id']]:0;
                                if(intval($student_data['price_paid']) <= 0 || (!empty($student_data['program_id']) && $student_data['payment_amount'] > 0 && $student_data['price_paid'] < intval($wholesale_price))){
                                    $warnings[$i] = [
                                        'Error' => intval($student_data['price_paid']) <= 0 ? 'Price not paid for this program.':'Price is less than Wholesale Price',
                                        'Program Name' => $program_id_name[$student_data['program_id']],
                                        'Wholesale Price' => $student_data['payment_amount'],
                                        'Price Paid' => $student_data['price_paid']
                                    ];
                                }

                                $pas_data[$i]['zoho_contact'] = Student::loadContactData($student_data, $states, $countries);
                                $pas_data[$i]['zoho_deals'] = Student::loadDealsData($student_data, $program_zid_name, $program_id_name, $states, $countries);

                                $programs_zoho = DB::table('pas_program')
                                    ->where('id', '=', $student_data['program_id'])
                                    ->get()->first();

                                $state_code = DB::table('pas_state')
                                    ->where('id', '=', $student_data['state'])
                                    ->value('iso2_code');

                                $country_name = DB::table('pas_country')
                                    ->where('id', '=', $student_data['country'])
                                    ->value('country_name');

                                $phone = null;
                                if(!empty($student_data['email'])){
                                    $phone = DB::raw('AES_ENCRYPT("'.$student_data['phone'].'", "'.$_ENV['AES_ENCRYPT_KEY'].'")');
                                }


                                $pas_data[$i]['pas_schedule'] = [
                                    'partner_id' => User::getPartnerDetail('id'),
                                    'partner_zoho_id' => User::getPartnerDetail('zoho_id'),
                                    'deal_name' => DB::raw('AES_ENCRYPT("'.$student_data['first_name'].' '.$student_data['last_name'].'", "'.$_ENV['AES_ENCRYPT_KEY'].'")'),
                                    'email' => DB::raw('AES_ENCRYPT("'.$student_data['email'].'", "'.$_ENV['AES_ENCRYPT_KEY'].'")'),
                                    'phone' => $phone,
                                    'stage' => 'Enrollment Processed',
                                    'payment_amount' => $programs_zoho ? $programs_zoho->retail_wholesale:null,
                                    'amount' => $student_data['price_paid'],
                                    'start_date' => $student_data['start_date'],
                                    'end_date' => $student_data['end_date'],
                                    'street' => $student_data['street'],
                                    'city' => $student_data['city'],
                                    'state' => $state_code,
                                    'zip' => $student_data['zip'],
                                    'country' => $country_name,
                                    'payment_type' => $student_data['payment_type'],
                                    'program_id' => $student_data['program_id'],
                                    'program_zoho_id' => $programs_zoho ? $programs_zoho->zoho_id:null,
                                    'created_at' => date('Y-m-d H:i:s')
                                ];

                                $pas_data[$i]['contact'] = [
                                    'partner_id' => User::getPartnerDetail('id'),
                                    'partner_zoho_id' => User::getPartnerDetail('zoho_id'),
                                    'first_name' => DB::raw('AES_ENCRYPT("' . $student_data['first_name'] . '", "' . $_ENV['AES_ENCRYPT_KEY'] . '")'),
                                    'last_name' => DB::raw('AES_ENCRYPT("' . $student_data['last_name'] . '", "' . $_ENV['AES_ENCRYPT_KEY'] . '")'),
                                    'email' => DB::raw('AES_ENCRYPT("' . $student_data['email'] . '", "' . $_ENV['AES_ENCRYPT_KEY'] . '")'),
                                    'phone' => !empty($student_data['phone']) ? DB::raw('AES_ENCRYPT("' . $student_data['phone'] . '", "' . $_ENV['AES_ENCRYPT_KEY'] . '")') : null,
                                    'contact_role' => 'Student',
                                    'mailing_city' => $student_data['city'],
                                    'mailing_state' => $state_code,
                                    'mailing_street' => $student_data['street'],
                                    'mailing_zip' => $student_data['zip'],
                                    'mailing_country' => $country_name,
                                    'created_at' => date('Y-m-d H:i:s')
                                ];
                            }
                        }

                        $check_enrollment = DB::table('pas_student')->where(DB::raw('AES_DECRYPT(email, "' . $_ENV['AES_ENCRYPT_KEY'] . '")'), $student_data['email'])->where('program_id', $student_data['program_id'])->get()->first();
                        if($check_enrollment){
                            $student_data['id'] = $check_enrollment->id;
                        }
                    }

                    // Data Encryption for Student Information
                    $student_data['first_name'] = DB::raw('AES_ENCRYPT("'.$student_data['first_name'].'", "'.$_ENV['AES_ENCRYPT_KEY'].'")');
                    $student_data['last_name'] = DB::raw('AES_ENCRYPT("'.$student_data['last_name'].'", "'.$_ENV['AES_ENCRYPT_KEY'].'")');
                    $student_data['email'] = DB::raw('AES_ENCRYPT("'.$student_data['email'].'", "'.$_ENV['AES_ENCRYPT_KEY'].'")');
                    $student_data['phone'] = DB::raw('AES_ENCRYPT("'.$student_data['phone'].'", "'.$_ENV['AES_ENCRYPT_KEY'].'")');
                    $student_data['created_at'] = date('Y-m-d H:i:s');
                    $student_data['created_by'] = Auth::user()->id;
                    $pas_data[$i]['pas_student'] = $student_data;

                }
                $i++;
            }

        Log::channel('enrollment')->info('Excel: Validate data END');

        //dump([count($errors), count($warnings), count($pas_data)]);
        //dd([$errors, $warnings, $pas_data]);

        $user_activity['action'] = 'create';
        $user_activity['old_data'] = json_encode($pas_data);
        $user_activity['new_data'] = json_encode($pas_data);
        UserActivityHelper::getInstance()->save($request, $user_activity);


        $last_ids = ['pas_schedule' => [], 'pas_student' => []];

        if(count($pas_data) > 0){
            try{
                foreach ($pas_data as $record) {
                    if(isset($record['pas_student']['id']) && $record['pas_student']['id'] > 0){
                        $id = $record['pas_student']['id'];
                        unset($record['pas_student']['id']);
                        DB::table('pas_student')
                            ->where('id', '=', $id)
                            ->update($record['pas_student']);
                        Log::channel('enrollment')->info('Excel: Record update into pas_student', $record['pas_student']);
                    }else{
                        DB::table('pas_student')->insert($record['pas_student']);
                        $last_ids['pas_student'][] = DB::getPdo()->lastInsertId();
                        Log::channel('enrollment')->info('Excel: Record inserted into pas_student', $record['pas_student']);
                    }

                    if(isset($record['zoho_leads']) && count($record['zoho_leads']) > 0){
                        Log::channel('enrollment')->info('Excel: ZOHO Leads data need to be updated', $record['zoho_leads']);
                        $lead_owner_email = $record['zoho_leads']['Owner_Email'];
                        unset($record['zoho_leads']['Owner_Email']);
                        $leads_response = ZohoHelper::getInstance()->updateRecord([$record['zoho_leads']], 'Leads');
                        if(isset($leads_response['status']) && $leads_response['status'] == 'error'){
                            Log::channel('enrollment')->info('Excel: ZOHO Leads record update failed', $leads_response);
                            return response()->json(array("status"=>"fail", "zoho_errors"=> $leads_response['message']));
                        }
                        Log::channel('enrollment')->info('Excel: ZOHO Leads updated: ', $leads_response);

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
                            Log::channel('enrollment')->info('Excel: ZOHO Contact fetch failed', $contact);
                            return response()->json(['status' => 'fail', 'errors' => $contact['message']]);
                        }

                        if(count($contact['data']) > 0 && isset($contact['data'][0]['id'])) {
                            Log::channel('enrollment')->info('Excel: ZOHO Contact exists so contact will not be created');
                            $record['zoho_deals']['Contact_Name']['id'] = $contact['data'][0]['id'];
                        }else {
                            Log::channel('enrollment')->info('Excel: ZOHO Contact data to be inserted', $record['zoho_contact']);
                            $contact_response = ZohoHelper::getInstance()->addRecord([$record['zoho_contact']], 'Contacts');
                            if(isset($contact_response['status']) && $contact_response['status'] == 'error'){
                                Log::channel('enrollment')->info('Excel: ZOHO Contact create failed', $contact_response);
                                return response()->json(array("status"=>"fail", "Excel: zoho_errors"=> $contact_response['message']));
                            }
                            Log::channel('enrollment')->info('Excel: ZOHO Contact created: ', $contact_response);

                            $record['zoho_deals']['Contact_Name']['id'] = $contact_response[0]['details']['id'];
                        }

                        $deals_response = ZohoHelper::getInstance()->addRecord([$record['zoho_deals']], 'Deals');
                        if(isset($deals_response['status']) && $deals_response['status'] == 'error'){
                            Log::channel('enrollment')->info('Excel: ZOHO Deals create failed', $deals_response);
                            return response()->json(array("status"=> "fail", "zoho_errors"=> $deals_response['message']));
                        }
                        $record['pas_schedule']['zoho_id'] = $deals_response[0]['details']['id'];
                        $record['pas_schedule']['contact_zoho_id'] = $record['zoho_deals']['Contact_Name']['id'];
                        DB::table('pas_schedule')->insert($record['pas_schedule']);
                        $last_ids['pas_schedule'][] = DB::getPdo()->lastInsertId();

                        if(isset($record['contact'])){
                            $contact_count = DB::table('pas_contact')
                                ->where('zoho_id', '=', $record['zoho_deals']['Contact_Name']['id'])
                                ->count('id');
                            if(empty($contact_count)){
                                $record['contact']['zoho_id'] = $record['zoho_deals']['Contact_Name']['id'];
                                DB::table('pas_contact')->insert($record['contact']);
                                Log::channel('enrollment')->info('Excel: Contact inserted into pas_contact', $record['contact']);
                            }
                        }

                        // Send Email Notification to logged in and Environment variable Users
                        (new Student())->sendStudentEnrollmentEmailNew($record['pas_student_email']);

                        //dd($deals_response);
                    }
                }
                Log::channel('enrollment')->info('Excel: CREATE NEW ENROLLMENT END');
                //return response()->json(['status' => 'success', 'msg' => 'Data added successfully.']);
            }catch (Exception $e){
                Log::channel('enrollment')->info('Excel: Exception on create Enrollment', $e->getMessage());
                return response()->json(array("status"=>"fail", "errors"=> $e->getMessage()));
            }

        }

        //(new Student())->sendBulkStudentLeadConvertToEnrollmentEmail($last_ids['pas_student']);
        // Send Email Notification to logged in and Environment variable Users
        (new Student())->sendBulkStudentEnrollmentEmail(count($last_ids['pas_student']) + count($last_ids['pas_schedule']));

        $skipped = [];
        $skipped_record_errors = [];
        foreach ($errors as $line_no => $error) {
            $skipped[] = $line_no;
            foreach ($error as $key => $skipped_error) {
                $skipped_record_errors[$line_no][Student::attributeNames($key)] = current($skipped_error);
            }
        }

        $log_data['file'] = $newfname;
        $log_data['date'] = date("Y-m-d");
        $log_data['partner_id'] = User::getPartnerDetail('id');
        $log_data['added_by'] = Auth::user()->id;
        $log_data['records_imported'] = count($last_ids['pas_student']);
        $log_data['records_imported_warning'] = count($warnings);
        $log_data['records_skiped'] = count($skipped);
        $log_data['file_size'] = $size;
        $log_data['processing_time'] = ((time() - $start_time) <= 0 ? 1:(time() - $start_time));
        $log_data['added_date'] = date("Y-m-d H:i:s");
        $log_data['warning_rows'] = count($warnings) > 0 ? json_encode($warnings):null;
        $log_data['skipped_rows'] = count($skipped_record_errors) > 0 ? json_encode($skipped_record_errors):null;

        DB::table('pas_imported_files')->insert($log_data);

        return response()->json([
            'status' => 'success',
            'success' => $log_data['records_imported'],
            'warning' => $log_data['records_imported_warning'],
            'skipped' => $log_data['records_skiped'],
            'error' => $skipped_record_errors,
            'zoho_errors' => []
        ]);

    }

        public function downloadImportAudit(Request $request){
            if(!empty(pas_decrypt($request->id)) && is_numeric(pas_decrypt($request->id))){
                $filename = DB::table('pas_imported_files')->where('id', '=', pas_decrypt($request->id))->value('file');
                if($filename){
                    $path = public_path('uploads/' . $filename);
                    if(!file_exists($path)){
                        return redirect(route('dashboard'));
                    }
                    return response()->download($path, $filename, [
                        'Content-Type' => 'application/vnd.ms-excel',
                        'Content-Disposition' => 'inline; filename="' . $filename . '"'
                    ]);
                }
            }
            return redirect(route('dashboard'));
        }

    public function importSkippedRecord(Request $request){

        if(!empty(pas_decrypt($request->id)) && is_numeric(pas_decrypt($request->id))){
            $file_detail = DB::table('pas_imported_files')->where('id', '=', pas_decrypt($request->id))->get()->first();

            if($file_detail){
                $filename = $file_detail->file;
                $path = public_path('uploads/' . $filename);
                if(!file_exists($path)){
                    //die("file doesn't exist.");
                    return redirect(route('dashboard'));
                }

                // Read file and return response.
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
                $xls_data = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
                //$xls_data = Student::getDummyData(); // Dummy data for testing purpose
                //print"<pre>";print_r($xls_data);die;

                /*if(!is_array($xls_data)){
                    return response()->json(['status' => 'fail', 'msg' => 'This file is invalid.']);
                }*/
                if($request->list_type == 'skipped'){
                    $skipped_lines = !empty($file_detail->skipped_rows) ? json_decode($file_detail->skipped_rows, true):[];
                }elseif($request->list_type == 'warning'){
                    $skipped_lines = !empty($file_detail->warning_rows) ? json_decode($file_detail->warning_rows, true):[];
                }

                $error_on_lines = array_keys($skipped_lines);
                //dd(array_keys($skipped_lines));
                $skipped_records = [];

                foreach ($xls_data as $index => $item) {
                    if(in_array($index, $error_on_lines)){
                        $item['errors'] = $skipped_lines[$index];
                        $skipped_records[] = $item;
                    }
                }

                $list_type = $request->list_type;
                //dd($skipped_records);
                return view('student.skipped-import-record', compact('skipped_records', 'list_type'));
            }
        }
        return redirect(route('dashboard'));
    }

    /*public function importWarningRecord(Request $request){

        if(!empty(pas_decrypt($request->id)) && is_numeric(pas_decrypt($request->id))){
            $file_detail = DB::table('pas_imported_files')->where('id', '=', pas_decrypt($request->id))->get()->first();

            if($file_detail){
                $filename = $file_detail->file;
                $path = public_path('uploads/' . $filename);
                if(!file_exists($path)){
                    //die("file doesn't exist.");
                    return redirect(route('dashboard'));
                }

                // Read file and return response.
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
                $xls_data = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

                $skipped_lines = !empty($file_detail->warning_rows) ? json_decode($file_detail->warning_rows, true):[];

                $error_on_lines = array_keys($skipped_lines);
                //dd(array_keys($skipped_lines));
                $skipped_records = [];

                foreach ($xls_data as $index => $item) {
                    if(in_array($index, $error_on_lines)){
                        $item['errors'] = $skipped_lines[$index];
                        $skipped_records[] = $item;
                    }
                }
                return view('student.warning-import-record', compact('skipped_records'));
            }
        }
        return redirect(route('dashboard'));
    }*/

}