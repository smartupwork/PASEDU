<?php
namespace App\Http\Controllers;
use App\CanvasHelper;
use App\Console\Commands\Canvas\CanvasRequest;
use App\EmailHelper;
use App\EmailRequest;
use App\Models\Partner;
use App\Models\Program;
use App\Models\User;
use App\Models\UserAccess;
use App\UserActivityHelper;
use App\Utility;
use App\ZohoHelper;
use App\Models\ListingSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPUnit\Exception;
use Session;
use Config;
use Lang;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Mpdf\Mpdf;
use Cookie;

require base_path("vendor/autoload.php");

class CatalogController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if(!UserAccess::hasAccess(UserAccess::CATALOG_MANAGEMENT_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $column_setting = ListingSetting::getCatalogDefaultListing();
        return view('catalog.index', compact('column_setting'));
    }

    public function search(Request $request) {
        if(!UserAccess::hasAccess(UserAccess::CATALOG_MANAGEMENT_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $available_programs = $this->getPartnerAvailablePrograms();
        //dd($available_programs);

        $result = $this->getSearchData($request);
        foreach($result as $key => $val){
            $result[$key]->_id = $val->id;
            $result[$key]->id = pas_encrypt($val->id);
            $result[$key]->status = $val->status;
        }
        /*return response()->json([
            'total_record' => $this->getSearchData($request, true),
            'result' => $result,
            'available_programs' => $available_programs,
        ]);*/
        $column_setting = ListingSetting::getCatalogDefaultListing();
        return view('catalog.view', compact('result','column_setting','available_programs'));
    }

    private function getPartnerAvailablePrograms(){
        if(!empty(User::getPartnerDetail('price_book_id'))){
            $available_programs = DB::table('pas_price_book')
                ->select(['pas_price_book_program_map.program_id'])
                ->leftJoin('pas_price_book_program_map', 'pas_price_book.id', '=', 'pas_price_book_program_map.price_book_id')
                ->where('price_book_id', '=', User::getPartnerDetail('price_book_id'))
                ->get()->all();
            return array_column($available_programs, 'program_id');
        }
        return [];
    }

    public function changeStatus(Request $request) {
        if(!UserAccess::hasAccess(UserAccess::CATALOG_MANAGEMENT_ACCESS, 'add')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }

        if(!empty(User::getPartnerDetail('price_book_id'))){
            $id = pas_decrypt($request->id);
            $program_zoho_id = $request->zoho_id;
            $program_list_price = $request->list_price;
            $zoho_price_book_id = User::getPartnerDetail('price_book_id');
            $zoho_price_book_zoho_id = User::getPartnerDetail('price_book_zoho_id');
            $action = $request->action;
            if(!empty($id) && !empty($zoho_price_book_id) && !empty($action)){
                $program_detail = DB::table('pas_program')
                    ->where('id', '=', $id)
                    ->get()->first();

                $res = [];
                if($action == 'add'){
                    $sub_account_response = $this->createSubAccountIfNotExists();
                    //dump($sub_account_response);
                    if($sub_account_response == null || !$sub_account_response['status']){
                        return response()->json($sub_account_response);
                    }
                    //For Test COURSE 'Agile Certified Practitioner Exam Prep'
                    $course_response = $this->createCanvasCourse($program_detail);

                    if($course_response == null || !$course_response['status']){
                        return response()->json($course_response);
                    }

                    $data = [
                        'price_book_id' => $zoho_price_book_id,
                        'price_book_zoho_id' => $zoho_price_book_zoho_id,
                        'program_id' => $id,
                        'program_zoho_id' => $program_zoho_id,
                        'program_list_price' => floatval($program_list_price),
                    ];
                    DB::table('pas_price_book_program_map')->insert($data);
                    ZohoHelper::getInstance()->updateRelatedRecord('Price_Books/'.$zoho_price_book_zoho_id, 'Products', ['id' => $program_zoho_id, 'list_price' => floatval($program_list_price)]);

                    $this->addCourseInShop($program_zoho_id, $program_list_price);

                    $res = ['status' => true, 'message' => 'Program added successful.'];
                }else if($action == 'remove'){
                    $this->removeCourseFromShop($program_zoho_id);

                    $row_affected = DB::table('pas_price_book_program_map')
                        ->where('program_id', '=', $id)
                        ->where('price_book_id', '=', $zoho_price_book_id)
                        ->delete();

                    if($row_affected > 0){
                        ZohoHelper::getInstance()->deleteRelatedRecord('Price_Books/'.$zoho_price_book_zoho_id, 'Products', [$program_zoho_id]);
                    }

                    $res = ['status' => true, 'message' => 'Program removed successful.'];
                }

                $log_data['action'] = $request->action == 'add' ? 'create':'delete';
                $log_data['old_data'] = json_encode($request->all());
                $log_data['new_data'] = json_encode($request->all());
                UserActivityHelper::getInstance()->save($request, $log_data);

                return response()->json($res);
            }
        }
        return response()->json(['status' => false, 'message' => 'Partner has not price book. Kindly assign price book first into ZOHO CRM Portal.']);
    }

    private function createSubAccountIfNotExists(){
            try{
                /*$exist_sub_account = DB::table('pas_canvas_sub_account')
                    ->where('parent_account_id', '=', User::getPartnerDetail('id'))
                    ->where('name', '=', User::getPartnerDetail('partner_name'))
                    ->get()->first();

                if($exist_sub_account){
                    return [
                        'status' => true,
                        'message' => 'Sub Account ('.User::getPartnerDetail('partner_name').') already exists.',
                    ];
                }*/

                $can_request = new CanvasRequest();
                $can_request->account_id = CanvasHelper::SUB_ACCOUNT;
                $sub_accounts = CanvasHelper::getInstance()->getSubAccountsOfAccount($can_request);

                $canvas_sub_account_id = null;
                foreach ($sub_accounts as $sub_account) {
                    if($sub_account['name'] == User::getPartnerDetail('partner_name')){
                        $canvas_sub_account_id = $sub_account['id'];
                    }
                }

                if(!$canvas_sub_account_id){
                    $can_request = new CanvasRequest();
                    $can_request->account_id = CanvasHelper::SUB_ACCOUNT;
                    $can_request->form_params = [ 'account[name]' => User::getPartnerDetail('partner_name') ];

                    $canvas_account_created = CanvasHelper::getInstance()->createSubAccountOfAccount($can_request);
                    if($canvas_account_created == null || !$canvas_account_created['status']){
                        return [
                            'status' => false,
                            'message' => 'Sub Account ('.User::getPartnerDetail('partner_name').') create failed.',
                        ];
                    }

                    $sub_act_data['parent_account_id'] = $canvas_account_created['data']['parent_account_id'];
                    $sub_act_data['sub_account_id'] = $canvas_account_created['data']['id'];
                    $sub_act_data['root_account_id'] = $canvas_account_created['data']['root_account_id'];
                    $sub_act_data['name'] = $canvas_account_created['data']['name'];
                    $sub_act_data['work_status'] = $canvas_account_created['data']['workflow_state'];
                    $sub_act_data['uuid'] = $canvas_account_created['data']['uuid'];
                    $sub_act_data['default_time_zone'] = $canvas_account_created['data']['default_time_zone'];
                    $sub_act_data['created_at'] = date('Y-m-d H:i:s');

                    DB::table('pas_canvas_sub_account')->insert($sub_act_data);
                    //$sub_account_id = DB::getPdo()->lastInsertId();
                    $canvas_sub_account_id = $sub_act_data['sub_account_id'];

                    $email_req = new EmailRequest();
                    $email_req->setTo([
                        [$_ENV['CANVAS_MASTER_COURSE_REQUEST'], User::getPartnerDetail('partner_name')],
                    ])
                        ->setSubject('Sub-account has been created for '.User::getPartnerDetail('partner_name'))
                        ->setBody(view('catalog.canvas-sub-account-email')->render())
                        ->setLogSave(true);

                    $email_helper = new EmailHelper($email_req);
                    $email_helper->sendEmail();

                }

                if($canvas_sub_account_id > 0){
                    DB::table('pas_partner')
                        ->where('id', '=', User::getPartnerDetail('id'))
                        ->update(['canvas_sub_account_id' => $canvas_sub_account_id]);

                    $this->updatePartnerSession();

                    return [
                        'status' => true,
                        'message' => 'Sub Account ('.User::getPartnerDetail('partner_name').') successfully created.',
                    ];
                }
                return [
                    'status' => false,
                    'message' => 'Canvas Sub Account ('.User::getPartnerDetail('partner_name').')  create failed.',
                ];
            }catch (Exception $e){
                return [
                    'status' => false,
                    'message' => $e->getMessage(),
                ];
            }

    }

    private function createCanvasCourse($program_detail) {
        if(empty(User::getPartnerDetail('canvas_sub_account_id'))){
           return [
               'status' => false,
               'message' => 'Partner canvas id does not exists.',
           ];
        }

        $course_suffix = 'NO-HOST';
        if(!empty(User::getPartnerDetail('hosted_site'))){
            $domain = parse_url(User::getPartnerDetail('hosted_site'));
            $course_suffix = explode('.', $domain['host'], -2);

            if(isset($course_suffix[0])){
                $course_suffix = $course_suffix[0];
            }
        }

        $can_request = new CanvasRequest();
        $can_request->account_id = User::getPartnerDetail('canvas_sub_account_id');
        $can_request->search_term = $program_detail->name.' '.$course_suffix;
        $course = CanvasHelper::getInstance()->getCoursesOfAccount($can_request);

        if(count($course) == 1) {
            $this->createOrUpdateIntoPas($course[0]);
            return [
                'status' => true,
                'message' => 'Course ('.$program_detail->name.' '.$course_suffix.') already exists into PAS.',
            ];
        }

        $can_request = new CanvasRequest();
        $can_request->account_id = 1;
        $can_request->search_term = $program_detail->name.' - Master';
        $can_request->include = ['teachers', 'term'];
        $master_courses = CanvasHelper::getInstance()->getCoursesOfAccount($can_request);

        if(count($master_courses) == 1){

            $sis_explode = explode('-', $master_courses[0]['sis_course_id']);
            if(count($sis_explode) > 1){
                $sis_explode[count($sis_explode)] = User::getPartnerDetail('canvas_sub_account_id');
            }

            $can_request = new CanvasRequest();
            $can_request->account_id = User::getPartnerDetail('canvas_sub_account_id');
            //$can_request->search_term = $program_detail->name;
            $can_request->form_params = [
                'course[name]' => $program_detail->name.' '.$course_suffix,
                'course[course_code]' => $program_detail->name,
                'course[sis_course_id]' => implode('-', $sis_explode),
                'course[is_public]' => false,
                'offer' => 'true',
                'course[license]' => 'private',
                //'course[teachers]' => isset($master_courses[0]['teachers'][0]) ? $master_courses[0]['teachers'][0]['id']: null,
                //'course[is_public_to_auth_users]' => true,
                //'course[event]' => 'offer',
                //'course[work_status]' => 'private',
                /*'start_at' => 1,
                'end_at' => 1,*/
            ];
            //dump($can_request->form_params);
            $course = CanvasHelper::getInstance()->createCoursesOfAccount($can_request);

            if($course && $course['status']){

                $this->createOrUpdateIntoPas($course['data']);

                $can_request = new CanvasRequest();
                $can_request->course_id = $course['data']['id'];
                $can_request->form_params = [
                    'migration_type' => 'course_copy_importer',
                    'settings[source_course_id]' => $master_courses[0]['id']
                ];
                $migrate_response = CanvasHelper::getInstance()->migrateCoursesContent($can_request);

                if(isset($master_courses[0]['teachers'][0])){
                    $can_request = new CanvasRequest();
                    $can_request->course_id = $course['data']['id'];
                    $can_request->form_params = [
                        'enrollment[user_id]' => $master_courses[0]['teachers'][0]['id'],
                        'enrollment[type]' => 'TeacherEnrollment',
                    ];
                    //dump($can_request->form_params);
                    CanvasHelper::getInstance()->coursesEnrolled($can_request);
                }

                if($migrate_response){
                    /*DB::table('pas_canvas_course')
                        ->where('id', '=', $canvas_course_id)
                        ->update( [ 'migration_detail' => json_encode($migrate_response) ]);*/

                    return [
                        'status' => true,
                        'message' => 'Course created and successfully copied master content.',
                    ];
                }
                return [
                    'status' => false,
                    'message' => 'Course copy ('.$master_courses[0]['name'].') => ('.$program_detail->name.' '.$course_suffix.') failed into Canvas.',
                ];
            }
        } else {
            $program_name = $program_detail->name.' '. $course_suffix;
            $email_req = new EmailRequest();
            $email_req->setTo([
                    [$_ENV['CANVAS_MASTER_COURSE_REQUEST'], "Pas Admin"],
                    //[$_ENV['DEVELOPER_EMAIL_SECOND'], "Info Xoom Web Development"],
                ])
                ->setSubject('Request to Create Master Course In Canvas')
                ->setBody(view('catalog.canvas-master-course-email', compact('program_name'))->render())
                ->setLogSave(true);

            $email_helper = new EmailHelper($email_req);
            $email_helper->sendEmail();

            return [
                'status' => false,
                'message' => 'Email sent to "'.$_ENV['CANVAS_MASTER_COURSE_REQUEST'].'" for create new Master Course',
            ];
        }
    }

    private function createOrUpdateIntoPas($course_detail){
        $course_in_db = DB::table('pas_canvas_course')
            ->where('canvas_course_id', '=', $course_detail['id'])
            ->get()->first();

        if($course_in_db) {
            DB::table('pas_canvas_course')
                ->where('id', '=', $course_in_db->id)
                ->update(['pas_sub_account_id' => User::getPartnerDetail('id')]);

            return $course_in_db->id;
        }else{
            $course_data = [
                'pas_sub_account_id' => User::getPartnerDetail('id'),
                'canvas_course_id' => $course_detail['id'],
                'account_id' => $course_detail['account_id'],
                'root_account_id' => $course_detail['root_account_id'],
                'name' => $course_detail['name'],
                'work_status' => $course_detail['workflow_state'],
                'uuid' => $course_detail['uuid'],
                'start_at' => $course_detail['start_at'],
                'end_at' => $course_detail['end_at'],
                'course_code' => $course_detail['course_code'],
                'license' => $course_detail['license'],
                'is_public' => $course_detail['is_public'] ? 1:0,
                'time_zone' => $course_detail['time_zone'],
                'created_at' => date('Y-m-d H:i:s'),
            ];

            DB::table('pas_canvas_course')->insert($course_data);
            return DB::getPdo()->lastInsertId();
        }
    }

    private function updatePartnerSession(){
        $partner = Partner::select(['id', \Illuminate\Support\Facades\DB::raw('CAST(zoho_id AS CHAR) AS zoho_id'), 'canvas_sub_account_id', 'partner_name', 'contact_name', 'hosted_site', 'title', 'phone', 'email', 'pi_phone', 'pi_email', 'department', 'wia', 'mycaa', 'street','city', 'state', 'zip_code', 'price_book_id', 'price_book_zoho_id', 'logo', 'status'])
            ->where('id', '=', User::getPartnerDetail('id'))
            ->get()->first()->toArray();

        Session::put('partner_detail', $partner);
    }


    public function exportExcel(Request $request) {
        if(!UserAccess::hasAccess(UserAccess::CATALOG_MANAGEMENT_ACCESS, 'download')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $available_programs = $this->getPartnerAvailablePrograms();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Program');
        $sheet->setCellValue('B1', 'Code');
        $sheet->setCellValue('C1', 'Hours');
        $sheet->setCellValue('D1', 'SRP');
        $sheet->setCellValue('E1', 'Wholesale');
        $sheet->setCellValue('F1', 'Type');
        $sheet->setCellValue('G1', 'Status');
        $sheet->setCellValue('H1', 'Description');
        $sheet->setCellValue('I1', 'Exam Included');

        $result = $this->getSearchData($request);

        $rows = 2;
        foreach($result as $val){
            $sheet->setCellValue('A' . $rows, $val->name);
            $sheet->setCellValue('B' . $rows, $val->code);
            $sheet->setCellValue('C' . $rows, $val->hours);
            $sheet->setCellValue('D' . $rows, '$'.$val->unite_price);
            $sheet->setCellValue('E' . $rows, $val->retail_wholesale);
            $sheet->setCellValue('F' . $rows, $val->program_type);
            $sheet->setCellValue('G' . $rows, (!in_array($val->id, $available_programs) ? 'Inactive':'Active'));
            $sheet->setCellValue('H' . $rows, $val->description);
            $sheet->setCellValue('I' . $rows, $val->certification_included);
            $rows++;
        }

        $filename = "catalog_lists.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save("export/".$filename);

        $headers = [
            'Content-length'      => $filename,
            'Content-type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename='{$filename}'",
        ];

        return response()->download("export/".$filename, $filename, $headers);
    }
    
    public function exportPdf(Request $request){
        if(!UserAccess::hasAccess(UserAccess::CATALOG_MANAGEMENT_ACCESS, 'download')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }

        $available_programs = $this->getPartnerAvailablePrograms();
        $result = $this->getSearchData($request);
        $hd = public_path('images/logo.png');
        $wt = public_path('images/bg.png');
        $str = '';
        $str .= '<div style="border:2px solid #666; padding:10px; font-family: arial, sans-serif;">';
        $str .= '<div style="text-align:center"><img src="'.$hd.'" style="width:600px" alt=""/></div>';
        $str .= '<div style="position: relative;">';
        //$str .= '<div style="text-align:center;"><img src="'.$wt.'" style="width:500px" alt=""/></div>';
        $str .= '<div style="position:absolute;top:0px;width:100%">';
        $str .= '<h2 style="padding:10px; font-family: arial, sans-serif;font-size: 16px;">Catalog Management</h2>';
        $str .= '<table style="width:100%;margin:0 auto;border-collapse: collapse;border:1px solid #333;">';
        $str .= '<tr>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:15%;font-weight:bold;text-align:left">Program</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Code</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Hours</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">SRP</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Wholesale</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:15%;font-weight:bold;text-align:left">Type</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Status</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Exam Included</th>';
        $str .= '</tr>';


        if(count($result) > 0){
            foreach($result as $val){
                $str .= '<tr>';
                $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->name.'</td>';
                $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->code.'</td>';
                $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->hours.'</td>';
                $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">$'.$val->unite_price.'</td>';
                $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->retail_wholesale.'</td>';
                $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->program_type.'</td>';
                $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.(!in_array($val->id, $available_programs) ? 'Inactive':'Active').'</td>';
                $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->certification_included.'</td>';
                $str .= '</tr>';
            }
        }else{
                $str .= '<tr>';
                $str .= '<td colspan="9" style="text-align:center;">No Record Found.</td>';
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
        return $mpdf->Output("Catalog_Management.pdf", 'D');
    }

    /**
     * @param Request $request
     * @param string $groupBy
     * @param bool $totalCount
     * @return \Illuminate\Support\Collection|int
     */
    private function getSearchData(Request $request, $totalCount = false){
        $query = DB::table('pas_program')
            ->select(['id', 'zoho_id', 'name', 'code', 'unite_price', 'hours', 'retail_wholesale', 'program_type', 'status', 'description', 'certification_included']);

        if (isset($request->q)){
            $query->where(function ($query) use ($request) {
                $query->orwhere('name', 'like', '%'.$request->q.'%')
                    ->orwhere('code', 'like', '%'.$request->q.'%')
                    ->orwhere('hours', 'like', '%'.$request->q.'%')
                    ->orwhere('hours', 'like', '%'.$request->q.'%')
                    ->orwhere('unite_price', 'like', '%'.$request->q.'%')
                    ->orwhere('program_type', 'like', '%'.$request->q.'%')
                    ->orwhere('certification_included', 'like', '%'.$request->q.'%')
                    ->orwhere('retail_wholesale', 'like', '%'.$request->q.'%')
                    ->orwhere('description', 'like', '%'.$request->q.'%')
                    ->orwhere('status', 'like', '%'.$request->q.'%');
            });
        }

        if (!empty($request->program_name)){
            $query->where('name', 'like', '%'.$request->program_name.'%');
        }
        if (!empty($request->course_code)){
            $query->where('code', 'like', '%'.$request->course_code.'%');
        }
        if (!empty($request->status)){
            $query->where('status', '=', $request->status);
        }
        if (!empty($request->program_type)){
            $query->where('program_type', '=', $request->program_type);
        }
        if (!empty($request->certification_included)){
            $query->where('certification_included', '=', $request->certification_included);
        }

        if(User::getPartnerDetail('zoho_id') != '1066248000069673003'){
            $query->where('layout', '!=', 'AoWE');
        }

        $query->where([
            ['status', '=', 'Active'],
            ['displayed_on', '=', 'All'],
            ['service_item_not_program', '=', 0],
            //['layout', '=', 'Standard'],
        ]);

        if($totalCount){
            return $query->count();
        }
        return $query->orderBy('id','DESC')->get();
    }

    /**
     * @param $program_zoho_id
     */
    private function removeCourseFromShop($program_zoho_id)
    {
        $partner_price_books = DB::table('pas_price_book_program_map AS ppm')
            //->select(['p.id', 'p.partner_name AS shop_name', 'p.price_book_zoho_id'])
            ->join('pas_partner AS p', 'p.price_book_zoho_id', '=', 'ppm.price_book_zoho_id')
            ->where('ppm.program_zoho_id', '=', $program_zoho_id)
            ->where('ppm.price_book_zoho_id', '=', User::getPartnerDetail('price_book_zoho_id'))
            ->pluck('p.partner_name')->toArray();

        $affiliate_price_books = DB::table('pas_price_book_program_map AS ppm')
            //->select(['a.id', 'a.affiliate_name AS shop_name', 'a.price_book_zoho_id'])
            ->join('pas_affiliate AS a', 'a.price_book_zoho_id', '=', 'ppm.price_book_zoho_id')
            ->where('ppm.program_zoho_id', '=', $program_zoho_id)
            ->where('ppm.price_book_zoho_id', '=', User::getPartnerDetail('price_book_zoho_id'))
            ->pluck('a.affiliate_name')->toArray();

        $partners = array_merge($partner_price_books, $affiliate_price_books);

        if (count($partners) > 0) {
            $id_shops = DB::connection('we_shop')->table('ps_shop')
                ->whereIn('name', $partners)
                ->pluck('id_shop')->toArray();

            if (count($id_shops) > 0) {
                $id_products = DB::connection('we_shop')
                    ->table('ps_product')
                    ->whereIn('id_shop_default', $id_shops)
                    ->where('zoho_id', '=', $program_zoho_id)
                    ->pluck('id_product')->toArray();

                if (!empty($id_products)) {
                    foreach ($id_shops as $id_shop) {
                        DB::connection('we_shop')->table('ps_product_shop')
                            ->where('id_shop', '=', $id_shop)
                            ->whereIn('id_product', $id_products)
                            ->delete();
                    }

                    Program::cacheClear();
                    Program::rebuildSearch();
                }
            }
        }
    }

    /**
     * @param $program_zoho_id
     * @param $program_list_price
     */
    private function addCourseInShop($program_zoho_id, $program_list_price)
    {
        $partners = DB::table('pas_partner')
            ->where('price_book_zoho_id', '=', User::getPartnerDetail('price_book_zoho_id'))
            ->pluck('partner_name')->toArray();

        $affiliates = DB::table('pas_affiliate')
            ->where('price_book_zoho_id', '=', User::getPartnerDetail('price_book_zoho_id'))
            ->pluck('affiliate_name')->toArray();

        $partners = array_merge($partners, $affiliates);

        $id_shops = DB::connection('we_shop')->table('ps_shop')
            ->whereIn('name', $partners)
            ->pluck('id_shop')->toArray();
        //dd([$program_zoho_id, $id_shops]);

        $product_shop_data = [];
        $re_index_products = [];
        foreach ($id_shops as $id_shop) {
            $id_product = DB::connection('we_shop')->table('ps_product')
                ->where('id_shop_default', '=', $id_shop)
                ->where('zoho_id', '=', $program_zoho_id)
                ->value('id_product');
            if (!empty($id_product)) {
                $re_index_products[] = $id_product;
                $product_shop_data[] = [
                    'id_product' => $id_product,
                    'id_shop' => $id_shop,
                    'price' => floatval($program_list_price),
                    'wholesale_price' => floatval($program_list_price),
                    'id_tax_rules_group' => 1,
                    'id_category_default' => 2,
                    'indexed' => 0,
                    'active' => 1,
                    //'is_best_selling' => $zoho_product['is_best_seller'],
                ];
            }

        }

        if (count($product_shop_data) > 0) {
            DB::connection('we_shop')->table('ps_product_shop')
                ->insert($product_shop_data);

            DB::connection('we_shop')->table('ps_product')
                ->whereIn('id_product', $re_index_products)
                ->update(['indexed' => 0]);

            Program::cacheClear();
            Program::rebuildSearch();
        }
    }
}