<?php
namespace App\Http\Controllers\Student;
use App\Http\Controllers\Controller;
use App\Models\MarketingCollateral;
use App\Models\UserNotification;
use App\Models\Roles;
use App\Models\User;
use App\Models\ListingSetting;
use App\Models\UserAccess;
use App\UserActivityHelper;
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

class MyinstitutionController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if(!UserAccess::hasAccess(UserAccess::MY_INSTITUTION_REQUEST_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }

        DB::table('pas_user_notification')->where([
                ['partner_id', '=', User::getPartnerDetail('id')],
                ['read_status', '=', UserNotification::UNREAD],
            ])->update([
                'read_status' => UserNotification::READ
            ]);

        $roles = Roles::where('role_type', '=', Roles::ROLE_TYPE_PARTNER)->get();
        $nrs = DB::table('pas_users')->where([["user_type", '=', '4']])->count();
        $column_setting = ListingSetting::getInstitutionDefaultListing();
        return view('student.myinstitution', compact('roles','nrs','column_setting'));
    }

    public function store(Request $request){
        if(!UserAccess::hasAccess(UserAccess::MY_INSTITUTION_REQUEST_ACCESS, 'add')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $idsarr = $request->ids;
        $old_status = $request->old_status;
        $statusarr = $request->status;
        for ($i = 0; $i < count($idsarr); $i++) {

            if($old_status[$i] != $statusarr[$i]){
                $idata['status'] = $statusarr[$i];
                $idata['updated_by'] = Auth::user()->id;
                $idata['updated_at'] = date('Y-m-d H:i:s');
                $idata['read_status'] = 'unread';
                DB::table('student_progress_report')->where([["id", '=', $idsarr[$i]]])->update($idata);
            }
        }

        $data['action'] = 'update';
        $data['old_data'] = json_encode($request->all());
        $data['new_data'] = json_encode($request->all());
        UserActivityHelper::getInstance()->save($request, $data);

        $newarray = array("status"=>"success");
        return response()->json($newarray);
    }

    public function ajax(Request $request) {
        if(!UserAccess::hasAccess(UserAccess::MY_INSTITUTION_REQUEST_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $result = $this->getSearchData($request);
        $data = array();
        foreach ($result as $val) {
            //$result[$key]->id = pas_encrypt($results->id);

            $data[] = [
                'request_type' => ($val->request_type == 1 ? 'Student Enrollment-Report Request': 'Marketing Collateral'),
                "requested_by"=>$val->firstname.' '.$val->lastname,
                "id"=>$val->id,
                "mid"=>pas_encrypt($val->id),
                "request_date"=>date(Utility::DEFAULT_DATE_FORMAT, strtotime($val->requested_date)),
                "request_time"=>date(Utility::DEFAULT_TIME_FORMAT_INSTITUTION, strtotime($val->requested_date)),
                "status"=>$val->status,
                "status_label"=>$val->status_label,
                "is_typical"=>$val->is_typical,
                "purpose"=>$val->purpose,
                "desired_completion_date"=> !empty($val->desired_completion_date) ? date(Utility::DEFAULT_DATE_FORMAT, strtotime($val->desired_completion_date)):null,
                "meeting_proposed_date"=> !empty($val->meeting_proposed_date) ? date(Utility::DEFAULT_DATE_FORMAT, strtotime($val->meeting_proposed_date)): null,
                'name' => ($val->request_type == 1 ? $val->subject: NULL),
                'program_name' => ($val->request_type == 1 ? $val->program_name: NULL),
                'username' => ($val->request_type == 1 ? $val->username: NULL)
            ];
        }
        $column_setting = ListingSetting::getInstitutionDefaultListing();
        return view('student._myinstitution_view', compact('data','column_setting'));
        //return response()->json($data);
    }

    public function exportexcel(Request $request) {
        if(!UserAccess::hasAccess(UserAccess::MY_INSTITUTION_REQUEST_ACCESS, 'download')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Request');
        $sheet->setCellValue('B1', 'Request Date');
        $sheet->setCellValue('C1', 'Request Time');
        $sheet->setCellValue('D1', 'Request By');
        $sheet->setCellValue('E1', 'Name');
        $sheet->setCellValue('F1', 'Program');
        $sheet->setCellValue('G1', 'Username');
        $sheet->setCellValue('H1', 'Purpose');
        $sheet->setCellValue('I1', 'Desired Completion Date');
        $sheet->setCellValue('J1', 'Meeting Proposed Date');
        $sheet->setCellValue('K1', 'Request Status');
        $result = $this->getSearchData($request);
        $rows = 2;//print"<pre>";print_r($result);die;
        if(count($result) > 0){
            foreach($result as $val){
                if($val->request_type == '1'){
                    $sbname = $val->subject;
                    $sbprogram = $val->program_name;
                    $sbusername = $val->username;
                }else{
                    $sbname = '';
                    $sbprogram = '';
                    $sbusername = '';
                }
                $sheet->setCellValue('A' . $rows, $val->is_typical);
                $sheet->setCellValue('B' . $rows, date(Utility::DEFAULT_DATE_FORMAT, strtotime($val->requested_date)));
                $sheet->setCellValue('C' . $rows, date(Utility::DEFAULT_TIME_FORMAT, strtotime($val->requested_date)));
                $sheet->setCellValue('D' . $rows, $val->firstname.' '.$val->lastname);
                $sheet->setCellValue('E' . $rows, $sbname);
                $sheet->setCellValue('F' . $rows, $sbprogram);
                $sheet->setCellValue('G' . $rows, $sbusername);
                $sheet->setCellValue('H' . $rows, $val->purpose);
                $sheet->setCellValue('I' . $rows, $val->desired_completion_date);
                $sheet->setCellValue('J' . $rows, $val->meeting_proposed_date);
                $sheet->setCellValue('K' . $rows, $val->status_label);
                $rows++;
            }
        }

        $filename = "myinstitution_lists.xlsx";
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

    public function exportpdf(Request $request){
        if(!UserAccess::hasAccess(UserAccess::MY_INSTITUTION_REQUEST_ACCESS, 'download')){
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
        $str .= '<h2 style="padding:10px; font-family: arial, sans-serif;font-size: 16px;">My Institution Request</h2>';
        $str .= '<table style="width:100%;margin:0 auto;border-collapse: collapse;border:1px solid #333;">';
        $str .= '<tr>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Request</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:8%;font-weight:bold;text-align:left">Request Date</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:8%;font-weight:bold;text-align:left">Request Time</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Request By</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Name</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Program</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:8%;font-weight:bold;text-align:left">Username</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:8%;font-weight:bold;text-align:left">Purpose</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:8%;font-weight:bold;text-align:left">Desired Completion Date</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Meeting Proposed Date</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Request Status</th>';
        $str .= '</tr>';

        if(count($result) > 0){
        foreach($result as $val){
            if($val->request_type == '1'){
                $sbname = $val->subject;
                $sbprogram = $val->program_name;
                $sbusername = $val->username;
            }else{
                $sbname = '';
                $sbprogram = '';
                $sbusername = '';
            }
            $str .= '<tr>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.($val->request_type == 1 ? 'Student Enrollment-Report Request': 'Marketing Collateral').'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.date(Utility::DEFAULT_DATE_FORMAT, strtotime($val->requested_date)).'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.date(Utility::DEFAULT_TIME_FORMAT, strtotime($val->requested_date)).'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->firstname.' '.$val->lastname.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$sbname.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$sbprogram.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$sbusername.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->purpose.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->desired_completion_date.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->meeting_proposed_date.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-bottom:1px solid #333;">'.$val->status_label.'</td>';
            $str .= '</tr>';

        }
        }else{
            $str .= '<tr>';
            $str .= '<td colspan="11" style="text-align:center;">No Record Found.</td>';
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
        return $mpdf->Output("My_Institution_Request.pdf", 'D');
    }

    private function getSearchData(Request $request){
        //dd(DB::getQueryLog());die; // last query
        $query = DB::table('student_progress_report AS r')
            ->leftJoin('pas_marketing_collateral as mc', function($join){
                $join->on('r.id', '=', 'mc.progress_report_id');
            })
            ->select('r.id', 'request_type', 'requested_date', 'r.status', 'firstname', 'lastname', 'is_typical', 'mc.purpose', 'mc.desired_completion_date', 'mc.meeting_proposed_date', DB::raw('CASE
            WHEN r.status = 1 THEN "Open" WHEN r.status = 2 THEN "Cancelled" WHEN r.status = 3 THEN "Completed" END AS status_label'),'pas_enrollment.subject','pas_enrollment.program_name','pas_enrollment.username')
            ->leftJoin('pas_users', function($join){
                $join->on('requested_by', '=', 'pas_users.id');
            }) ->leftJoin('pas_enrollment', function($join){
                $join->on('r.student_id', '=', 'pas_enrollment.id');
            });

        $query->where('r.partner_id', '=', User::getPartnerDetail('id'));

        if (isset($request->q)){
            $query->where(function ($query) use ($request) {
                $query->orwhere('r.status', 'like', '%'.$request->q.'%')
                    ->orwhere('firstname', 'like', '%'.$request->q.'%')
                    ->orwhere('lastname', 'like', '%'.$request->q.'%')
                    ->orwhere('mc.purpose', 'like', '%'.$request->q.'%');

                   /* $date = \DateTime::createFromFormat(Utility::DEFAULT_DATE_FORMAT, $request->q);
                    if($date && $date->format(Utility::DEFAULT_DATE_FORMAT) === $request->q){
                        $query->orwhere(Db::raw('DATE(requested_date)'), '=', date('Y-m-d', strtotime($request->q)));
                    }*/

                    $query->orwhere(Db::raw('(DATE_FORMAT(requested_date, "%m/%d/%Y"))'), 'like', "%".$request->q."%");
                    $query->orwhere(Db::raw('(DATE_FORMAT(requested_date, "%h %p"))'), 'like', "%".$request->q."%");
                $request_type = Utility::searchKeywordIntoArray(strtolower($request->q), MarketingCollateral::getRequestType());
                if(count($request_type) > 0){
                    $query->orWhereIn('r.request_type', $request_type);
                }
                //request_date
            });

            /*$project_type = Utility::searchKeywordIntoArray($request->q, MarketingCollateral::getProjectType());
            if(count($project_type) > 0){
                $query->orWhereIn('mc.project_type', $project_type);
            }*/

        }
        //if(Auth::user()->roleid != '1'){

        //}
        $query->orderBy('id','desc');
        return $query->get();
        //dd(DB::getQueryLog());die; // last query
    }


}
