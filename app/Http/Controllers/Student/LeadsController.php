<?php
namespace App\Http\Controllers\Student;
use App\EmailHelper;
use App\EmailRequest;
use App\Http\Controllers\Controller;
use App\Models\EmailTemplates;
use App\Models\Leads;
use App\Models\ListingSetting;
use App\Models\Program;
use App\Models\Roles;
use App\Models\Country;
use App\Models\State;
use App\Models\Timezone;
use App\Models\User;
use App\Models\UserAccess;
use App\UserActivityHelper;
use App\Utility;
use App\ZohoHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Session;
use Config;
use Lang;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
require base_path("vendor/autoload.php");
use Mpdf\Mpdf;
use Cookie;


class LeadsController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if(!UserAccess::hasAccess(UserAccess::LEADS_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $roles = Roles::where('role_type', '=', Roles::ROLE_TYPE_PARTNER)->get();
        $nrs = DB::table('pas_users')->where([["user_type", '=', '4']])->count();
        $column_setting = ListingSetting::getLeadsDefaultListing();

        return view('student.leads', compact('roles','nrs', 'column_setting'));
    }

    public function add(){
        if(!UserAccess::hasAccess(UserAccess::LEADS_ACCESS, 'add')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $programs = Program::orderBy('name', 'ASC')->get()->toArray();
        $roles = Roles::where([['role_type', '=', Roles::ROLE_TYPE_PARTNER], ['status', '=', Utility::STATUS_ACTIVE]])->get();
        $countries = Country::where('status', '=', 1)->get();
        $states = State::where('status', '=', 1)->get();
        $timezone = Timezone::orderBy("display_order",'asc')->get();
       return view('student.leadsentry', compact('roles','countries', 'timezone', 'states', 'programs'));
    }

    public function store(Request $request){
        if(!UserAccess::hasAccess(UserAccess::LEADS_ACCESS, 'add')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $id = pas_decrypt($request->id);
        $idata = array();
        $idata['partner_id'] = User::getPartnerDetail('id');
        $idata['partner_institution'] = User::getPartnerDetail('partner_name');
        $idata['name_of_requester'] = Auth::user()->firstname.' '.Auth::user()->lastname;
        $idata['email_of_requester'] = Auth::user()->email;
        $idata['firstname'] = $request->firstname;
        $idata['lastname'] = $request->lastname;
        $idata['email'] = $request->email;
        $idata['address'] = $request->address;
        $idata['phone'] = $request->phone;
        $idata['city'] = $request->city;
        $idata['state'] = $request->state;
        $idata['zip'] = $request->zip;
        $idata['country'] = $request->country;
        $idata['interested_program'] = $request->interested_program;
        $idata['financing_needs'] = $request->financing_needs;
        $idata['category_of_interest'] = $request->category_interest;
        $idata['time_zone'] = $request->time_zone;
        $idata['inquiry_message'] = $request->message;
        $idata['added_by'] = Auth::user()->id;
        $idata['added_date'] = date("Y-m-d H:i:s");
        if(User::getPartnerDetail('id') == ''){
            $newarray = array("status"=>"fail", "msg"=>"Partner institution is required.");
        }elseif($request->name_requester == ''){
            $newarray = array("status"=>"fail","msg"=>"Please enter name of requester.");
        }elseif($request->email_requester == ''){
            $newarray = array("status"=>"fail","msg"=>"Please enter email of requester.");
        }elseif(!filter_var($request->email_requester, FILTER_VALIDATE_EMAIL)){
            $newarray = array("status"=>"fail","msg"=>"Please enter valid email of requester.");
        }elseif($request->firstname == ''){
            $newarray = array("status"=>"fail","msg"=>"Please enter first name.");
        }elseif($request->lastname == ''){
            $newarray = array("status"=>"fail","msg"=>"Please enter last name.");
        }elseif($request->email == ''){
            $newarray = array("status"=>"fail","msg"=>"Please enter email.");
        }elseif(!filter_var($request->email, FILTER_VALIDATE_EMAIL)){
            $newarray = array("status"=>"fail","msg"=>"Please enter valid email.");
        }else{
            if($id > 0){
                DB::table('pas_leads')->where([["id", '=', $id]])->update($idata);
                $newarray = array("status"=>"success","msg"=>"Record updated successfully.","lid"=>"");
            }else{
                $owner = DB::table('pas_owner')->inRandomOrder()
                    ->where('status', '=', 'active')
                    ->where('email', '!=', 'info@xoomwebdevelopment.com')
                    ->first();

                $country_name = null;
                if(!empty($request->country)){
                    $country_name = Country::where('id', '=', $request->country)->value('country_name');
                }

                $state_iso2 = null;
                if(!empty($request->state)) {
                    $state_iso2 = State::where('id', '=', $request->state)->value('iso2_code');
                }

                $time_zone_name = null;
                if(!empty($request->time_zone)){
                    $time_zone = Timezone::where('id', '=', $request->time_zone)->get()->first();
                    $time_zone_name = $time_zone->timezone;
                }

                $programs = Program::get()->toArray();
                $program_id_name = array_column($programs, 'name', 'id');
                $program_zid_name = array_column($programs, 'zoho_id', 'id');
                //$program_unit_price = array_column($programs, 'unite_price', 'id');

                // Program Information
                $zoho_leads[0]['School_Id'] = User::getPartnerDetail('zoho_id');
                $zoho_leads[0]['School'] = User::getPartnerDetail('zoho_id');
                //$zoho_leads[0]['Program'] = $request->program_name;

                if(!empty($request->interested_program)){
                    $zoho_leads[0]['Program_of_Interest_ID'] = isset($program_zid_name[$request->interested_program]) ? $program_zid_name[$request->interested_program]:null;

                    //$zoho_leads[0]['Program'] = $program_zid_name[$request->interested_program];
                    $zoho_leads[0]['Program']['name'] = isset($program_id_name[$request->interested_program]) ? $program_id_name[$request->interested_program]:null;
                    $zoho_leads[0]['Program']['id'] = isset($program_zid_name[$request->interested_program]) ? $program_zid_name[$request->interested_program]:null;
                }

                // Student Lead Information
                $zoho_leads[0]['First_Name'] = $request->firstname;
                $zoho_leads[0]['Last_Name'] = $request->lastname;
                $zoho_leads[0]['Email'] = $request->email;
                $zoho_leads[0]['Phone'] = $request->phone;
                $zoho_leads[0]['Time_Zone'] = $time_zone_name;

                // Address Information
                $zoho_leads[0]['Street'] = $request->address;
                $zoho_leads[0]['City'] = $request->city;
                $zoho_leads[0]['State'] = $state_iso2;
                $zoho_leads[0]['Zip_Code'] = $request->zip;
                $zoho_leads[0]['Country'] = $country_name;

                // Inquiry Information
                $zoho_leads[0]['Inquiry_Message'] = $request->message;
                $zoho_leads[0]['Financing_Needs'] = $request->financing_needs;
                $zoho_leads[0]['Category_of_Interest'] = $request->category_interest;


                $zoho_leads[0]['Lead_Origin'] = 'PAS Lead Form';
                $zoho_leads[0]['Lead_Source_new'] = 'Client';
                $zoho_leads[0]['Lead_Segment'] = 'PAS Site';
                //$zoho_leads[0]['Owner'] = 'World Education';
                //$zoho_leads[0]['Owner'] = $_ENV['OWNER_ID']; //Kathryn Browne  kat@academyofwe.com
                $zoho_leads[0]['Owner'] = $owner ? $owner->zoho_id: $_ENV['OWNER_ID'];
                $zoho_leads[0]['Lead_Stage'] = 'Active';
                $zoho_leads[0]['Lead_Status'] = 'New';

                // Check Leads already exists than Update Leads
                $criteria = [
                    ['Email', 'equals', $request->email],
                ];

                $lead = ZohoHelper::getInstance()->fetchCriteria('Leads', [], 1, 1, $criteria);

                if (isset($lead['status']) && $lead['status'] == 'error') {
                    return response()->json(['status' => 'fail', 'msg' => $lead['message']]);
                }

                //dd($zoho_leads);
                $zoho_response = ZohoHelper::getInstance()->addRecord($zoho_leads, 'Leads');
                if(isset($zoho_response['status']) && $zoho_response['status'] == 'error'){
                    if (count($lead['data']) > 0 && isset($lead['data'][0]['id'])) {
                        $this->createLeadNotes($lead['data'][0]['id'], $zoho_leads);
                    }
                    return response()->json(array("status"=>"fail", "msg"=> $zoho_response['message']));
                }
                //dump($zoho_response);die;
                if(isset($zoho_response[0]['details']['id']) && !empty($zoho_response[0]['details']['id'])){
                    $this->createLeadNotes($zoho_response[0]['details']['id'], $zoho_leads);
                    $idata['zoho_id'] = $zoho_response[0]['details']['id'];
                    $idata['owner_zoho_id'] = $owner ? $owner->zoho_id: $_ENV['OWNER_ID'];
                    DB::table('pas_leads')->insert($idata);
                    $last_id = DB::getPdo()->lastInsertId();
                    $idata['interested_program'] = isset($zoho_leads[0]['Program']['name']) ? $zoho_leads[0]['Program']['name']:'';
                    $this->sendLeadsEntryEmail($idata, $owner);
                    $leeds_data['action'] = 'create';
                    $leeds_data['old_data'] = json_encode($zoho_leads);
                    $leeds_data['new_data'] = json_encode($zoho_leads);
                    $leeds_data['ref_ids'] = $last_id;
                    UserActivityHelper::getInstance()->save($request, $leeds_data);
                }
                $newarray = array("status"=>"success","msg"=>"Record added successfully.","lid"=>"");
            }

        }
        return response()->json($newarray);
    }

    private function createLeadNotes($zoho_id, $zoho_leads){
        /*// Check Leads already exists than Update Leads
        $criteria = [
            ['Email', 'equals', $request->email],
        ];

        $lead = ZohoHelper::getInstance()->fetchCriteria('Leads', [], 1, 1, $criteria);

        if (isset($lead['status']) && $lead['status'] == 'error') {
            return response()->json(['status' => 'fail', 'msg' => $lead['message']]);
        }

        if (count($lead['data']) > 0 && isset($lead['data'][0]['id'])) {*/
            $item['zoho_id'] = $zoho_id;


            $note_data['Note_Title'] = 'Leads Update from PAS';
            $note_content = '';
            foreach ($zoho_leads[0] as $key => $zoho_lead) {
                if(!is_array($zoho_lead) && $key != 'id' && $key != 'School_Id' && $key != 'Program_Id'){
                    $note_content .= str_replace('_', ' ', $key).': '.$zoho_lead.''.PHP_EOL;
                }
            }

            $note_data['Note_Content'] = $note_content;
            $note_data['Parent_Id'] = $zoho_id;
            $note_data['se_module'] = 'Leads';
            $notes_response = ZohoHelper::getInstance()->addRecord([$note_data], 'Notes');
            if(isset($notes_response['status']) && $notes_response['status'] == 'error'){
                return response()->json(array("status" => "fail", "msg"=> $notes_response['message']));
            }
        //}
    }

    public function sendLeadsEntryEmail($lead, $owner){
        $placeholder['FIRST_NAME'] = $lead['firstname'];
        $placeholder['LAST_NAME'] = $lead['lastname'];
        $placeholder['NAME_OF_REQUESTER'] = $lead['name_of_requester'];
        $placeholder['PARTNER_NAME'] = $lead['partner_institution'];
        $placeholder['PROGRAM_NAME'] = $lead['interested_program'];
        $placeholder['EMAIL'] = $lead['email'];
        $placeholder['INQUIRY_MESSAGE'] = $lead['inquiry_message'];
        $placeholder['PHONE'] = $lead['phone'];

        $email_req = new EmailRequest();
        $email_req->setTemplate(EmailTemplates::LEADS_ENTRY_TO_OWNER)
            ->setPlaceholder($placeholder)
            /*->setFromName($_ENV['FROM_NAME'])
            ->setFromEmail($_ENV['FROM_EMAIL'])*/
            ->setTo([
                [$_ENV['ADMIN_EMAIL_FIRST'], 'PAS Admin'],
                [$_ENV['ADMIN_EMAIL_SECOND'], 'PAS Admin'],
            ])
            ->setLogSave(true);

        $email_helper = new EmailHelper($email_req);
        $email_helper->sendEmail();

        $placeholder_owner['STUDENT_NAME'] = $lead['firstname'].' '.$lead['lastname'];
        $placeholder_owner['INSTITUTION'] = $lead['partner_institution'];
        $placeholder_owner['PROGRAM_NAME'] = $lead['interested_program'];

        $email_req = new EmailRequest();
        $email_req->setTemplate(EmailTemplates::LEADS_ENTRY_TO_OWNER)
            ->setPlaceholder($placeholder_owner)
            /*->setFromName($_ENV['FROM_NAME'])
            ->setFromEmail($_ENV['FROM_EMAIL'])*/
            ->setTo([
                [$owner->email, $owner->full_name],
            ])
            ->setLogSave(true);

        $email_helper = new EmailHelper($email_req);
        $email_helper->sendEmail();
    }

    public function ajax(Request $request) {
        if(!UserAccess::hasAccess(UserAccess::LEADS_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $column_setting = ListingSetting::getLeadsDefaultListing();
        //dd($column_setting);
        $result = $this->getSearchData($request);
        foreach ($result as $key => $results) {
            $result[$key]->id = pas_encrypt($results->id);
        }
        return view('student.leads-test', compact('result','column_setting'));
    }


    /*public function edit(Request $request){
        if(!UserAccess::hasAccess(UserAccess::LEADS_ACCESS, 'add')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $id = pas_decrypt($request->id);
        if(empty($id)){
            return redirect()->route('leads')->with('error', 'Invalid ID.');
        }
        $edata = DB::table('pas_leads')->select(['id', 'zoho_id', DB::raw('partner_name AS partner_institution'), 'name_of_requester', 'email_of_requester', 'firstname', 'lastname', 'email', 'address', 'phone', 'city', 'state', 'zip', 'country', 'interested_program', 'financing_needs', 'category_of_interest', 'time_zone', 'inquiry_message', 'added_by', 'added_date'])
            ->join('pas_partner', function($join){
                $join->on('partner_id', '=', 'pas_partner.id');
            })
            ->where([["id", '=', pas_decrypt($request->id)]])
            ->first();

        if(!$edata){
            return redirect()->route('my-users')->with('error', 'Record not found.');
        }
        $countries = Country::where('status', '=', 1)->get();
        $states = State::where('status', '=', 1)->get();
        $timezone = Timezone::orderBy("display_order",'asc')->get();
        $roles = Roles::where([['role_type', '=', Roles::ROLE_TYPE_PARTNER], ['status', '=', Utility::STATUS_ACTIVE]])->get();

        $programs = Program::orderBy('name', 'ASC')->get()->toArray();
        return view('student.leadsedit', compact('edata', 'roles', 'countries', 'timezone', 'states', 'programs'));
    }*/

    public function view(Request $request){
        if(!UserAccess::hasAccess(UserAccess::LEADS_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $id = pas_decrypt($request->id);
        if(empty($id)){
            return redirect(route('leads'));
        }
        $lead = DB::table('pas_leads as l')
            ->select('l.id', 'l.firstname', 'l.lastname', 'l.phone', 'l.email', DB::raw('partner_name AS partner_institution'), 'name_of_requester', 'email_of_requester', 'address', Db::raw('name AS interested_program'), 'financing_needs', 'category_of_interest', 'inquiry_message', Db::raw('country_name AS country'), Db::raw('state_name AS state'), 'l.city', 'zip', 'timezone')
            ->where([["l.id", '=', pas_decrypt($request->id)]])
            ->join('pas_partner', function($join){
                $join->on('partner_id', '=', 'pas_partner.id');
            })
            ->leftJoin('pas_program', function($join){
                $join->on('interested_program', '=', 'pas_program.id');
            })->leftJoin('pas_country', function($join){
                $join->on('country', '=', 'pas_country.id');
            })->leftJoin('pas_state', function($join){
                $join->on('l.state', '=', 'pas_state.id');
            })->leftJoin('pas_timezone', function($join){
                $join->on('time_zone', '=', 'pas_timezone.id');
            })
            ->first();
        if(!$lead){
            return redirect(route('my-users'));
        }

        return view('student.leads-view', compact('lead'));
    }


    public function delete(Request $request){
        if(!UserAccess::hasAccess(UserAccess::LEADS_ACCESS, 'add')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $ids = request('id');
        $ids_arr = @explode(',', $ids);

        if(count($ids_arr) > 0){
            $ids_arr = array_filter(array_map('pas_decrypt', $ids_arr));
            DB::table('pas_leads')->whereIn('id', $ids_arr)->delete();
        }
        $newarray = array("status"=>"success");
        return response()->json($newarray);
    }


    public function exportexcel(Request $request) {
        if(!UserAccess::hasAccess(UserAccess::LEADS_ACCESS, 'download')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'First Name');
        $sheet->setCellValue('B1', 'Last Name');
        $sheet->setCellValue('C1', 'Email');
        $sheet->setCellValue('D1', 'Partner Institution');
        $sheet->setCellValue('E1', 'Name Of Requester');
        $sheet->setCellValue('F1', 'Email Of Requester');
        $sheet->setCellValue('G1', 'Phone');
        $sheet->setCellValue('H1', 'Address');
        $sheet->setCellValue('I1', 'Country');
        $sheet->setCellValue('J1', 'State');
        $sheet->setCellValue('K1', 'City');
        $sheet->setCellValue('L1', 'Zip COde');
        $sheet->setCellValue('M1', 'Interested Program');
        $sheet->setCellValue('N1', 'Financing Needs');
        $sheet->setCellValue('O1', 'Category Of Interest');
        $sheet->setCellValue('P1', 'Time Zone');
        $sheet->setCellValue('Q1', 'Inquiry Message');
        $result = $this->getSearchData($request);
        $rows = 2;//print"<pre>";print_r($result);die;
        if(count($result) > 0){
            foreach($result as $val){
                $sheet->setCellValue('A' . $rows, $val->firstname);
                $sheet->setCellValue('B' . $rows, $val->lastname);
                $sheet->setCellValue('C' . $rows, $val->email);
                $sheet->setCellValue('D' . $rows, $val->partner_institution);
                $sheet->setCellValue('E' . $rows, $val->name_of_requester);
                $sheet->setCellValue('F' . $rows, $val->email_of_requester);
                $sheet->setCellValue('G' . $rows, $val->phone);
                $sheet->setCellValue('H' . $rows, $val->address);
                $sheet->setCellValue('I' . $rows, $val->country);
                $sheet->setCellValue('J' . $rows, $val->state);
                $sheet->setCellValue('K' . $rows, $val->city);
                $sheet->setCellValue('L' . $rows, $val->zip);
                $sheet->setCellValue('M' . $rows, $val->interested_program);
                $sheet->setCellValue('N' . $rows, $val->financing_needs);
                $sheet->setCellValue('O' . $rows, $val->category_of_interest);
                $sheet->setCellValue('P' . $rows, $val->timezone);
                $sheet->setCellValue('Q' . $rows, $val->inquiry_message);
                $rows++;
            }
        }

        $filename = "leads_lists.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save("export/".$filename);

        //ob_end_clean(); // this is solution
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

    public function exportpdf(Request $request){
        if(!UserAccess::hasAccess(UserAccess::LEADS_ACCESS, 'download')){
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
        $str .= '<h2 style="padding:10px; font-family: arial, sans-serif;font-size: 16px;">Leads</h2>';
        $str .= '<table style="width:100%;margin:0 auto;border-collapse: collapse;border:1px solid #333;">';
        $str .= '<tr>';
        $str .= '<th width="8%" style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;font-weight:bold;text-align:left">First Name</th>';
        $str .= '<th width="8%" style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;font-weight:bold;text-align:left">Last Name</th>';
        $str .= '<th width="10%" style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;font-weight:bold;text-align:left">Email</th>';
        $str .= '<th width="10%" style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;font-weight:bold;text-align:left">Partner Institution</th>';
        $str .= '<th width="10%" style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;font-weight:bold;text-align:left">Name Of Requester</th>';
        $str .= '<th width="10%" style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;font-weight:bold;text-align:left">Email Of Requester</th>';
        $str .= '<th width="8%" style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;font-weight:bold;text-align:left">Phone</th>';
        $str .= '<th width="10%" style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;font-weight:bold;text-align:left">Address</th>';
        $str .= '<th width="8%" style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;font-weight:bold;text-align:left">Country</th>';
        $str .= '<th width="8%" style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;font-weight:bold;text-align:left">State</th>';
        $str .= '<th width="8%" style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;font-weight:bold;text-align:left">City</th>';
        $str .= '<th width="8%" style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;font-weight:bold;text-align:left">Zip</th>';
        $str .= '<th width="10%" style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;font-weight:bold;text-align:left">Interested Program</th>';
        $str .= '<th width="8%" style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;font-weight:bold;text-align:left">Financing Needs</th>';
        $str .= '<th width="8%" style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;font-weight:bold;text-align:left">Category Of Interest</th>';
        $str .= '<th width="8%" style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;font-weight:bold;text-align:left">Time Zone</th>';
        $str .= '<th width="10%" style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-bottom:1px solid #333;font-weight:bold;text-align:left">Inquiry Message</th>';
        $str .= '</tr>';

        if(count($result) > 0){
        foreach($result as $val){
            $str .= '<tr>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->firstname.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->lastname.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->email.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->partner_institution.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->name_of_requester.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->email_of_requester.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->phone.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->address.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->country.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->state.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->city.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->zip.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->interested_program.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->financing_needs.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->category_of_interest.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->timezone.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-bottom:1px solid #333;">'.$val->inquiry_message.'</td>';
            $str .= '</tr>';

        }
        }else{
            $str .= '<tr>';
            $str .= '<td colspan="17" style="text-align:center;">No Record Found.</td>';
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
        return $mpdf->Output("Leads.pdf", 'D');
    }

    private function getSearchData(Request $request){
        //dd(DB::getQueryLog());die; // last query
        $query = DB::table('pas_leads AS l')->select('l.id', 'l.firstname', 'l.lastname', 'l.phone', 'l.email', Db::raw('partner_name AS partner_institution'), 'name_of_requester', 'email_of_requester', 'pas_partner.phone AS partner_phone', 'address', Db::raw('name AS interested_program'), 'financing_needs', 'category_of_interest', 'inquiry_message', Db::raw('country_name AS country'), Db::raw('state_name AS state'), 'l.city', 'l.zip', 'timezone')
            ->join('pas_partner', function($join){
                $join->on('partner_id', '=', 'pas_partner.id');
            })->leftJoin('pas_program', function($join){
                $join->on('interested_program', '=', 'pas_program.id');
            })->leftJoin('pas_country', function($join){
                $join->on('country', '=', 'pas_country.id');
            })->leftJoin('pas_state', function($join){
                $join->on('l.state', '=', 'pas_state.id');
            })->leftJoin('pas_timezone', function($join){
                $join->on('time_zone', '=', 'pas_timezone.id');
            });

        if (isset($request->q)){
            $query->where(function ($query) use ($request) {
                $query->orwhere('l.firstname', 'like', '%'.$request->q.'%')
                    ->orwhere('l.lastname', 'like', '%'.$request->q.'%')
                    ->orwhere('l.email', 'like', '%'.$request->q.'%')
                    ->orwhere('l.partner_institution', 'like', '%'.$request->q.'%')
                    ->orwhere('l.name_of_requester', 'like', '%'.$request->q.'%')
                    ->orwhere('l.email_of_requester', 'like', '%'.$request->q.'%')
                    ->orwhere('l.phone', 'like', '%'.$request->q.'%')
                    ->orwhere('l.address', 'like', '%'.$request->q.'%')
                    ->orwhere('country', 'like', '%'.$request->q.'%')
                    ->orwhere('l.interested_program', 'like', '%'.$request->q.'%')
                    ->orwhere('l.financing_needs', 'like', '%'.$request->q.'%')
                    ->orwhere('l.category_of_interest', 'like', '%'.$request->q.'%')
                    ->orwhere('l.city', 'like', '%'.$request->q.'%')
                    ->orwhere('l.zip', 'like', '%'.$request->q.'%')
                    ->orwhere('state_name', 'like', '%'.$request->q.'%')
                    ->orwhere('pas_program.name', 'like', '%'.$request->q.'%')
                    ->orwhere('timezone', 'like', '%'.$request->q.'%');
            });
        }

        if (!empty($request->firstname))
            $query->where('l.firstname', '=', $request->firstname);
        if (!empty($request->lastname))
            $query->where('l.lastname', '=', $request->lastname);
        if (!empty($request->email))
            $query->where('l.email', 'like', '%'.$request->email.'%');
        if (!empty($request->partner_institution))
            $query->where('l.partner_institution', 'like', '%'.$request->partner_institution.'%');
        if (!empty($request->name_requester))
            $query->where('l.name_of_requester', 'like', '%'.$request->name_requester.'%');
        if (!empty($request->email_requester))
            $query->where('l.email_of_requester', 'like', '%'.$request->email_requester.'%');

        if(User::getPartnerDetail('id')){
            $query->where('partner_id', '=', User::getPartnerDetail('id'));
        }

        return $query->orderBy('id', 'DESC')->get();
        //dd(DB::getQueryLog());die; // last query
    }


    public function headerupdate(Request $request){
        $input = $request->all();
        $position_arr = [];
        foreach ($input['position'] as $position => $column) {
            $position_arr[$column] = $position;
        }
        //print"<pre>";print_r($position_arr);die;

        $listing_setting = DB::table('listing_setting')
            //->where('partner_id', '=', User::getPartnerDetail('id'))
            ->where('user_id', '=', Auth::user()->id)
            ->where('module', '=', $request->module);

        $data['user_id'] = Auth::user()->id;
        $data['partner_id'] = User::getPartnerDetail('id');
        $data['module'] = $request->module;
        $data['menu'] = json_encode([
            'column_position' => $position_arr,
            'visible_columns' => $request->is_visible
        ]);


        if($listing_setting->count('id') > 0){
            $data['updated_at'] = date('Y-m-d H:i:s');
            $listing_setting->update($data);
        }else{
            $data['created_at'] = date('Y-m-d H:i:s');
            DB::table('listing_setting')->insert($data);
        }

        return response()->json(['status' => 'success', 'msg' => 'Setting successfully updated.']);
    }


}
