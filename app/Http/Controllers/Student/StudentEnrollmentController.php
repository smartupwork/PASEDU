<?php
namespace App\Http\Controllers\Student;
use App\CanvasHelper;
use App\Console\Commands\Canvas\CanvasRequest;
use App\EmailHelper;
use App\EmailRequest;
use App\Http\Controllers\Controller;
use App\Models\CanvasUserEnrolement;
use App\Models\Roles;
use App\Models\User;
use App\Models\ListingSetting;
use App\Models\UserAccess;
use App\Utility;
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

class StudentEnrollmentController extends Controller
{
    private $data = ['update' => []];
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (!UserAccess::hasAccess(UserAccess::STUDENT_ENROLLMENT_ACCESS, 'view')) {
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }

        $pll_access = UserAccess::hasAccess(UserAccess::STUDENT_PII_ACCESS, 'view');
        $roles = Roles::where('role_type', '=', Roles::ROLE_TYPE_PARTNER)->get();
        $column_setting = ListingSetting::getStudentDashboardDefaultListing();
        return view('student.enrollment.index', compact('roles', 'pll_access', 'column_setting'));
    }

    public function search(Request $request)
    {
        $result = $this->getSearchData($request);
        foreach ($result as $key => $val) {
            $result[$key]->id = pas_encrypt($val->id);
        }
        $column_setting = ListingSetting::getStudentDashboardDefaultListing();
        return view('student.enrollment.view', compact('result', 'column_setting'));
        //return response()->json(['total_record' => $this->getSearchData($request, true), 'result' => $result]);
    }

    public function exportExcel(Request $request)
    {
        if (!UserAccess::hasAccess(UserAccess::STUDENT_ENROLLMENT_ACCESS, 'download')) {
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Subject');
        $sheet->setCellValue('B1', 'Partner Name');
        $sheet->setCellValue('C1', 'Status');
        $sheet->setCellValue('D1', 'Grand Total');
        $sheet->setCellValue('E1', 'Start Date');
        $sheet->setCellValue('F1', 'Program');
        $sheet->setCellValue('G1', 'Completion Date');
        $sheet->setCellValue('H1', 'End Date	');
        $sheet->setCellValue('I1', 'Final Grade');
        $sheet->setCellValue('J1', 'User Name');

        $result = $this->getSearchData($request);
        $rows = 2;//print"<pre>";print_r($result);die;
        if (count($result) > 0) {
            foreach ($result as $val) {
                $sheet->setCellValue('A' . $rows, $val->subject);
                $sheet->setCellValue('B' . $rows, $val->partner_name);
                $sheet->setCellValue('C' . $rows, $val->status);
                $sheet->setCellValue('D' . $rows, $val->grand_total);
                $sheet->setCellValue('E' . $rows, $val->start_date);
                $sheet->setCellValue('F' . $rows, $val->program_name);
                $sheet->setCellValue('G' . $rows, $val->completion_date);
                $sheet->setCellValue('H' . $rows, $val->end_date);
                $sheet->setCellValue('I' . $rows, $val->final_grade);
                $sheet->setCellValue('J' . $rows, $val->username);
                $rows++;
            }
        }
        $filename = "importaudit_list.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save("export/" . $filename);

        //ob_end_clean(); // this is solution
        header('Content-Description: File Transfer');
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"" . basename($filename) . "\"");
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        readfile("export/" . $filename);
        unlink("export/" . $filename);
    }

    public function exportPdf(Request $request)
    {
        if (!UserAccess::hasAccess(UserAccess::STUDENT_ENROLLMENT_ACCESS, 'download')) {
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $result = $this->getSearchData($request);

        $hd = public_path('images/logo.png');
        $wt = public_path('images/bg.png');
        $str = '';
        $str .= '<div style="border:2px solid #666; padding:10px; font-family: arial, sans-serif;">';
        $str .= '<div style="text-align:center"><img src="' . $hd . '" style="width:600px" alt=""/></div>';
        $str .= '<div style="position: relative;">';
        //$str .= '<div style="text-align:center;"><img src="'.$wt.'" style="width:500px" alt=""/></div>';
        $str .= '<div style="position:absolute;top:0px;width:100%">';
        $str .= '<h2 style="padding:10px; font-family: arial, sans-serif;font-size: 16px;">Student Dashboard</h2>';
        $str .= '<table style="width:100%;margin:0 auto;border-collapse: collapse;border:1px solid #333;">';
        $str .= '<tr>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Subject</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Partner Name</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Status</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Grand Total</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Start Date</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Program</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Completion Date</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">End Date</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Final Grade</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Username</th>';
        $str .= '</tr>';

        if (count($result) > 0) {
            foreach ($result as $val) {
                $str .= '<tr>';
                $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">' . $val->subject . '</td>';
                $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">' . $val->partner_name . '</td>';
                $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">' . $val->status . '</td>';
                $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">' . $val->grand_total . '</td>';
                $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">' . $val->start_date . '</td>';
                $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">' . $val->program_name . '</td>';
                $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">' . $val->completion_date . '</td>';
                $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">' . $val->end_date . '</td>';
                $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">' . $val->final_grade . '</td>';
                $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">' . $val->username . '</td>';
                $str .= '</tr>';

            }
        } else {
            $str .= '<tr>';
            $str .= '<td colspan="10" style="text-align:center;">No Record Found.</td>';
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

        $mpdf->SetWatermarkImage($wt, 0.4, '', array(40, 50));
        $mpdf->showWatermarkImage = true;
        $mpdf->WriteHTML($str);
        return $mpdf->Output("Student_Dashboard.pdf", 'I');
    }

    private function getSearchData(Request $request, $totalCount = false)
    {
        //dd(DB::getQueryLog());die; // last query

        if (UserAccess::hasAccess(UserAccess::STUDENT_PII_ACCESS, 'view')) {
            $dob = DB::raw('DATE_FORMAT(AES_DECRYPT(pas_contact.date_of_birth, "' . $_ENV['AES_ENCRYPT_KEY'] . '"), "' . Utility::DEFAULT_DATE_FORMAT_MYSQL . '") AS date_of_birth');
            $ssn = DB::raw('AES_DECRYPT(pas_contact.social_security_number, "' . $_ENV['AES_ENCRYPT_KEY'] . '") AS social_security_number');
        } else {
            $dob = DB::raw('"" AS date_of_birth');
            $ssn = DB::raw('"" AS social_security_number');
        }

        //dd($pll_has_access);
        $query = DB::table('pas_enrollment')->select('pas_enrollment.id', 'subject', 'partner_name', 'pas_enrollment.status', 'grand_total', Db::raw('DATE_FORMAT(start_date, "' . Utility::DEFAULT_DATE_FORMAT_MYSQL . '") AS start_date'), 'program_name', Db::raw('DATE_FORMAT(completion_date, "' . Utility::DEFAULT_DATE_FORMAT_MYSQL . '") AS completion_date'), Db::raw('DATE_FORMAT(end_date, "' . Utility::DEFAULT_DATE_FORMAT_MYSQL . '") AS end_date'), 'final_grade', 'username', $dob, $ssn)
            ->leftJoin('pas_contact', 'pas_contact.id', '=', 'pas_enrollment.contact_id')
            ->leftJoin('pas_partner', 'pas_partner.id', 'pas_enrollment.partner_id');

        if (isset($request->q)) {
            $query->where(function ($query) use ($request) {
                $query->orwhere('subject', 'like', '%' . $request->q . '%')
                    ->orwhere('partner_name', 'like', '%' . $request->q . '%')
                    ->orwhere('pas_enrollment.status', 'like', '%' . $request->q . '%')
                    ->orwhere('grand_total', 'like', '%' . $request->q . '%')
                    ->orwhere('program_name', 'like', '%' . $request->q . '%')
                    ->orwhere(Db::raw('(DATE_FORMAT(start_date, "%m/%d/%Y"))'), 'like', "%" . $request->q . "%")
                    ->orwhere(Db::raw('(DATE_FORMAT(completion_date, "%m/%d/%Y"))'), 'like', "%" . $request->q . "%")
                    ->orwhere(Db::raw('(DATE_FORMAT(end_date, "%m/%d/%Y"))'), 'like', "%" . $request->q . "%")
                    ->orwhere('final_grade', 'like', '%' . $request->q . '%')
                    ->orwhere('username', 'like', '%' . $request->q . '%');

            });
        }

        if (!empty($request->subject))
            $query->where('subject', 'like', "%" . $request->subject . "%");
        if (!empty($request->status))
            $query->where('pas_enrollment.status', 'like', "%" . $request->status . "%");
        if (!empty($request->program))
            $query->where('program_name', 'like', "%" . $request->program . "%");
        if (!empty($request->username))
            $query->where('username', '=', $request->username);

        $query->where('pas_enrollment.partner_id', User::getPartnerDetail('id'));

        if ($totalCount) {
            return $query->count();
        }
        $query->orderBy('pas_enrollment.start_date', 'DESC');
        return $query->get();
        //dd(DB::getQueryLog());die; // last query
    }

    /*public function delete(){
        if(!UserAccess::hasAccess(UserAccess::STUDENT_IMPORT_AUDIT_ACCESS, 'add')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $ids = request('id');
        $ids_arr = @explode(',', $ids);

        if(count($ids_arr) > 0){
            $ids_arr = array_filter(array_map('pas_decrypt', $ids_arr));
            DB::table('pas_imported_files')->whereIn('id', $ids_arr)->delete();
        }
        $newarray = array("status"=>"success");
        return response()->json($newarray);
    }*/

    public function popup(Request $request)
    {
        if (!UserAccess::hasAccess(UserAccess::STUDENT_MANAGEMENT_ACCESS, 'add')) {
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }

        $canvas_student_id = null;
        $student_exists_on_canvas = false;
        $can_request = new CanvasRequest();
        $can_request->account_id = CanvasHelper::SUB_ACCOUNT;
        $sub_accounts = CanvasHelper::getInstance()->getSubAccountsOfAccount($can_request);

        $canvas_sub_account_id = null;
        foreach ($sub_accounts as $sub_account) {
            if($sub_account['name'] == User::getPartnerDetail('partner_name')){
                $canvas_sub_account_id = $sub_account['id'];
            }
        }

        if($canvas_sub_account_id){
            $can_req = new CanvasRequest();
            $can_req->account_id = $canvas_sub_account_id;
            $can_req->search_term = $request->student_name;

            $user_search = CanvasHelper::getInstance()->getUsersOfAccount($can_req);

            if(!isset($user_search['errors']) && count($user_search) == 1){
                $canvas_student_id = $user_search[0]['id'];
                $student_exists_on_canvas = true;
            }
        }

        $enrollment_id = $request->enrollment_id;
        $activity_type = $request->activity_type;
        $activity_progress = DB::table('student_activity_progress')
            ->where('activity_type', '=', $activity_type)
            ->where('enrollment_id', '=', pas_decrypt($enrollment_id))
            ->get()->first();

        /*if($activity_progress){
            $student_exists_on_canvas = true;
        }*/
        return view('student.popup', compact('enrollment_id', 'activity_type', 'activity_progress', 'student_exists_on_canvas', 'canvas_student_id'));
    }

    public function activityReportStore(Request $request)
    {
        if (!UserAccess::hasAccess(UserAccess::STUDENT_MANAGEMENT_ACCESS, 'add')) {
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }

        if(!isset($request->report_type)){
            return response()->json(["status" => "fail", "message" => "Please select".($request->canvas_student_id ? ' generate or':'')." schedule report."]);
        }

        if($request->report_type == 'schedule-report' && !isset($request->schedule_interval)){
            return response()->json(["status" => "fail", "message" => "Please select schedule interval."]);
        }

        if($request->activity_type == 'activity-progress' && $request->report_type == 'generate-report' && $request->fetch_report_type == 'date-range'){
            if ($request->fetch_report_type == 'date-range' && empty($request->fetch_start_date)) {
                return response()->json(["status" => "fail", "message" => "Please select start date."]);
            }

            if ($request->fetch_report_type == 'date-range' && empty($request->fetch_end_date)) {
                return response()->json(["status" => "fail", "message" => "Please select end date."]);
            }
        }

        $enrollment_id = pas_decrypt($request->enrollment_id);
        $report_type = $request->report_type;
        $schedule_at = null;
        if($request->schedule_interval == 'bi-week'){
            $schedule_at = date('Y-m-d', strtotime('+2 week'));
        }else if($request->schedule_interval == 'one-month'){
            $schedule_at = date('Y-m-d', strtotime('+1 month'));
        }else if($request->schedule_interval == 'six-month'){
            $schedule_at = date('Y-m-d', strtotime('+6 month'));
        }else if($request->schedule_interval == 'one-time' && !empty($request->scheduled_at)){
            $schedule_at = date('Y-m-d', strtotime($request->scheduled_at));
        }

        if($request->activity_type == 'activity-progress' && $request->fetch_report_type == 'date-range'){
            if(!empty($request->fetch_start_date)){
                $data['fetch_start_date'] = date('Y-m-d', strtotime($request->fetch_start_date));
            }
            if(!empty($request->fetch_end_date)){
                $data['fetch_end_date'] = date('Y-m-d', strtotime($request->fetch_end_date));
            }
        }else{
            $data['fetch_start_date'] = null;
            $data['fetch_end_date'] = null;
        }

        $data['canvas_student_id'] = $request->canvas_student_id;
        $data['enrollment_id'] = $enrollment_id;
        $data['activity_type'] = $request->activity_type;
        $data['report_type'] = $request->report_type;
        $data['schedule_interval'] = $report_type == 'schedule-report' ? $request->schedule_interval : null;
        $data['scheduled_at'] = $schedule_at;
        $data['fetch_report_type'] = !empty($request->fetch_report_type) ? $request->fetch_report_type: 'all';
        $data['is_recurring'] = (isset($request->is_recurring) || $request->schedule_interval == 'one-time') ? 1 : 0;
        $data['partner_id'] = User::getPartnerDetail('id');

        if (($data['report_type'] == 'schedule-report' && $data['schedule_interval'] == 'one-time') && empty($data['scheduled_at'])) {
            return response()->json(["status" => "fail", "message" => "Please select one time date."]);
        }

//dd($data);
        if(!empty($request->activity_progress_id)){
            $data['updated_by'] = Auth::user()->id;
            $data['updated_at'] = date("Y-m-d H:i:s");
            DB::table('student_activity_progress')
                ->where('id', '=', $request->activity_progress_id)
                ->update($data);
            $last_id = $request->activity_progress_id;
        }else{
            $data['created_by'] = Auth::user()->id;
            $data['created_at'] = date("Y-m-d H:i:s");
            DB::table('student_activity_progress')->insert($data);
            $last_id = DB::getPdo()->lastInsertId();
        }

        if ($last_id > 0) {
            $url = '';
            //if($request->report_type == 'generate-report'){
                if($request->activity_type == 'activity-log'){
                    $report_type = 'Program Progress Report';
                    if($request->report_type == 'generate-report') {
                        $url = route('student-activity-progress', ['enrollment_id' => $enrollment_id]);
                    }
                }else if($request->activity_type == 'activity-progress'){
                    $report_type = 'Activity Progress Report';
                    if($request->report_type == 'generate-report') {
                        $url = route('student-activity-log', ['enrollment_id' => $enrollment_id, 'start_date' => $data['fetch_start_date'], 'end_date' => $data['fetch_end_date']]);
                    }
                }
            //}

            if(empty($request->canvas_student_id)){
                $activity_progress = DB::table('student_activity_progress')
                    ->select(['activity_type', 'report_type', 'schedule_interval', 'scheduled_at', DB::raw('pas_partner.email AS partner_email'), 'pas_partner.partner_name', 'pas_enrollment.subject', DB::raw('AES_DECRYPT(pas_contact.email, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS email'), DB::raw('AES_DECRYPT(pas_contact.phone, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS phone'), 'program_name', 'fetch_report_type', 'fetch_start_date', 'fetch_end_date'])
                    ->join('pas_partner', 'pas_partner.id', '=', 'student_activity_progress.partner_id')
                    ->join('pas_enrollment', 'pas_enrollment.id', '=', 'student_activity_progress.enrollment_id')
                    ->leftJoin('pas_contact', 'pas_contact.id', '=', 'pas_enrollment.contact_id')
                    ->join('pas_program', 'pas_program.zoho_id', '=', 'pas_enrollment.program_zoho_id')
                    ->where('student_activity_progress.id', '=', $last_id)
                    //->where('pas_partner.id', '=', 15)
                    ->get()->first();

                $this->sendReportEmail($activity_progress, $report_type);
                return response()->json(["status" => "success", "message" => "Request has been submitted. You will receive the report soon", 'url' => $url]);
            }
            return response()->json(["status" => "success", "message" => "Request successfully submitted.", 'url' => $url]);
        } else {
            return response()->json(["status" => "fail", "message" => "Request submit failed."]);
        }
    }

    private function sendReportEmail($activity_progress, $report_type){
        //dump($report_type.' Request');
        //die(view('student.enrollment.email-activity-report', compact('activity_progress', 'report_type'))->render());
        $email_req = new EmailRequest();
        $email_req
            ->setTo([
                [$_ENV['ADMIN_EMAIL_FIRST'], $activity_progress->partner_name],
                [$_ENV['HELP_DESK_EMAIL'], $activity_progress->partner_name],
                [Auth::user()->email, Auth::user()->firstname.' '.Auth::user()->lastname],
            ])
            ->setCc([
                [$_ENV['PARTNER_EMAIL'], $activity_progress->partner_name]
            ])
            ->setSubject($report_type.' Request')
            ->setBody(view('student.enrollment.email-activity-report', compact('activity_progress', 'report_type'))->render())
            ->setLogSave(true);

        $email_helper = new EmailHelper($email_req);
        $email_helper->sendEmail();
    }

    public function activityLog(Request $request)
    {
        $enrollment = DB::table('pas_enrollment')
            ->select(['pas_partner.partner_name', 'pas_enrollment.subject', DB::raw('AES_DECRYPT(pas_contact.email, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS email'), DB::raw('CONCAT(AES_DECRYPT(pas_contact.first_name, "'.$_ENV['AES_ENCRYPT_KEY'].'")," ", AES_DECRYPT(pas_contact.last_name, "'.$_ENV['AES_ENCRYPT_KEY'].'")) AS student_name'), DB::raw('AES_DECRYPT(pas_contact.phone, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS phone'), 'program_name', 'start_date', 'end_date', 'hours'])
            ->leftJoin('pas_contact', 'pas_contact.id', '=', 'pas_enrollment.contact_id')
            ->join('pas_partner', 'pas_partner.id', '=', 'pas_enrollment.partner_id')
            ->join('pas_program', 'pas_program.zoho_id', '=', 'pas_enrollment.program_zoho_id')
            ->where('pas_enrollment.id', '=', $request->enrollment_id)
            //->where('pas_partner.id', '=', 15)
            ->get()->first();
//dd($enrollment);
        if(!$enrollment){
            return 'Enrollment not found.';
        }

        /*$can_req = new CanvasRequest();
        $can_req->account_id = 1;
        $can_req->search_term = $enrollment->subject;

        $user_search = CanvasHelper::getInstance()->getUsersOfAccount($can_req);

        if(count($user_search) == 0 || count($user_search) > 1){
            dd(count($user_search).' user account found with '.$enrollment->subject.' name');
        }*/

        $user_search = DB::table('pas_canvas_user')->where('name', '=', $enrollment->subject)->get()->first();
        if(!$user_search){
            return 'Canvas user not found.';
            //return redirect(route('student-enrollment'));
        }

        //dd($user_search->canvas_user_id);

        $query = DB::table('pas_canvas_user_enrollment')
            ->select(['pas_canvas_course.name', 'pas_canvas_user_enrollment.enroll_start_date', 'pas_canvas_user_enrollment.enroll_end_date', 'last_activity_at', 'today_activity_sec', 'report_at', 'login_time', 'ip_address'])
            ->join('pas_canvas_course', 'pas_canvas_course.canvas_course_id', '=', 'pas_canvas_user_enrollment.course_id')
            ->where('pas_canvas_user_enrollment.user_id', '=', $user_search->canvas_user_id)
            //->where('pas_canvas_user_enrollment.report_at', '<', date('Y-m-d'))
            ->where('pas_canvas_user_enrollment.today_activity_sec', '>', 0)
            ->orderBy('pas_canvas_user_enrollment.course_id', 'ASC')
            ->orderBy('pas_canvas_user_enrollment.report_at', 'ASC');

        if(!empty($request->start_date) && !empty($request->end_date)){
            $query->where([
                ['pas_canvas_user_enrollment.report_at', '>=', $request->start_date],
                ['pas_canvas_user_enrollment.report_at', '<=', $request->end_date],
            ]);
        }else{
            $query->where('pas_canvas_user_enrollment.report_at', '<', date('Y-m-d'));
        }

        $courses_activities = $query->get()->all();

        /*$courses = DB::table('pas_canvas_user_enrollment')
            ->where('user_id', '=', $user_search->canvas_user_id)
            ->where(DB::raw('DATE(created_at)'), '=', date('Y-m-d'))
            ->get()->all();*/

        //if(count($courses_activities) == 0){
            //return 'User has no enrollments.';
            //return redirect(route('student-enrollment'));
        //}

        $mpdf = new mPDF([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 5,
            'margin_right' => 5,
            'margin_top' => 5,
            'margin_bottom' => 5
        ]);

        $mpdf->SetWatermarkImage(public_path('images/bg.png'),0.4,'',array(40,50));
        $mpdf->showWatermarkImage = true;
        $mpdf->WriteHTML(view('student.enrollment.student-activity-log', compact('user_search', 'enrollment', 'courses_activities')));
        return $mpdf->Output("Activity-Progress-Report-".date("d-m-Y").".pdf", 'D');

    }

    public function activityProgress(Request $request){

        $enrollment = DB::table('pas_enrollment')
            ->select(['pas_partner.partner_name', 'pas_enrollment.subject', DB::raw('CONCAT(AES_DECRYPT(pas_contact.first_name, "'.$_ENV['AES_ENCRYPT_KEY'].'")," ", AES_DECRYPT(pas_contact.last_name, "'.$_ENV['AES_ENCRYPT_KEY'].'")) AS student_name'), DB::raw('AES_DECRYPT(pas_contact.email, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS email'), DB::raw('AES_DECRYPT(pas_contact.phone, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS phone'), 'program_name', 'start_date', 'end_date', 'hours'])
            ->leftJoin('pas_contact', 'pas_contact.id', '=', 'pas_enrollment.contact_id')
            ->join('pas_partner', 'pas_partner.id', '=', 'pas_enrollment.partner_id')
            ->join('pas_program', 'pas_program.zoho_id', '=', 'pas_enrollment.program_zoho_id')
            ->where('pas_enrollment.id', '=', $request->enrollment_id)
            //->where('pas_partner.id', '=', 15)
            ->get()->first();

        if(!$enrollment){
            return 'Enrollment not found.';
            //return redirect(route('student-enrollment'));
        }

        /*$can_req = new CanvasRequest();
        $can_req->account_id = 1;
        $can_req->search_term = $enrollment->subject;

        $user_search = CanvasHelper::getInstance()->getUsersOfAccount($can_req);

        if(count($user_search) == 0 || count($user_search) > 1){
            dd(count($user_search).' user account found with '.$enrollment->subject.' name');
        }*/

        $user_search = DB::table('pas_canvas_user')->where('name', '=', $enrollment->subject)->get()->first();
        if(!$user_search){
            return 'Canvas user not found.';
            //return redirect(route('student-enrollment'));
        }

        $user_courses = DB::table('pas_canvas_user_enrollment')
            ->orderBy('id', 'DESC')
            ->where('user_id', '=', $user_search->canvas_user_id)->get()->first();

        //dd($user_courses);

        $course_module_list = [];
        if($user_courses){
            $can_req = new CanvasRequest();
            $can_req->course_id = $user_courses->course_id;
            //$can_req->course_id = 1083;
            //$can_req->include = ['items', 'content_details'];
            $can_req->query_params = [
                //'user_id' => 699,
                'user_id' => $user_search->canvas_user_id,
            ];
            $grade_feeds = CanvasHelper::getInstance()->courseGradeBookFeed($can_req);
            //echo '<pre>';print_r($grade_feeds);die;

            $final_result = [];
            if(count($grade_feeds) > 0){
                foreach ($grade_feeds as $grade_feed){
                    if(!isset($final_result[$grade_feed['assignment_id']])){
                        $final_result[$grade_feed['assignment_id']] = $grade_feed['current_grade'];
                    }
                }
            }
            //echo '<pre>';print_r($final_result);die;

            $can_req = new CanvasRequest();
            $can_req->course_id = $user_courses->course_id;
            //$can_req->course_id = 1083;
            $can_req->include = ['items', 'content_details'];
            $can_req->query_params = [
                //'student_id' => 699,
                'student_id' => $user_search->canvas_user_id,
            ];
            $course_modules = CanvasHelper::getInstance()->courseModules($can_req);
            //echo '<pre>';print_r($course_modules);die;

            foreach ($course_modules as $course_module) {
                $counter = 0;
                $course_module_list[$course_module['id']]['id'] = $course_module['id'];
                $course_module_list[$course_module['id']]['items_count'] = $course_module['items_count'];
                $course_module_list[$course_module['id']]['name'] = $course_module['name'];
                $course_module_list[$course_module['id']]['grade'] = '';
                $course_module_list[$course_module['id']]['completed'] = 0;

                foreach ($course_module['items'] as $item){
                    if(isset($item['content_id']) && isset($final_result[$item['content_id']])){
                        //echo $item['content_id'].' => '.$final_result[$item['content_id']].'<br>';
                        $course_module_list[$course_module['id']]['grade'] = $final_result[$item['content_id']];
                        //$course_module_list[$course_module['id']]['items_count'] = $course_module['items_count'];
                    }
                    if(isset($course_module['state']) && $course_module['state'] = 'completed'){
                        $counter++;
                        $course_module_list[$course_module['id']]['completed'] = $counter;
                    }/*else{
                    $course_module_list[$course_module['id']]['completed'] = 0;
                }*/
                }
            }
        }

        //echo '<pre>';print_r($course_module_list);die;

        $mpdf = new mPDF([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 5,
            'margin_right' => 5,
            'margin_top' => 5,
            'margin_bottom' => 5
        ]);

        $mpdf->SetWatermarkImage(public_path('images/bg.png'),0.4,'', array(40,50));
        $mpdf->showWatermarkImage = true;
        $mpdf->WriteHTML(view('student.enrollment.student-activity-progress', compact('user_search', 'enrollment', 'course_module_list')));
        return $mpdf->Output("Program-Progress-Report-".date("d-m-Y").".pdf", 'D');
    }

}
