<?php
namespace App\Http\Controllers\Dashboard;
use App\EmailHelper;
use App\EmailRequest;
use App\Http\Controllers\Controller;
use App\Models\EmailTemplates;
use App\Models\Roles;
use App\Models\User;
use App\Models\UserAccess;
use App\Models\ListingSetting;
use App\UserActivityHelper;
use App\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Session;
use Config;
use Lang;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
require base_path("vendor/autoload.php");
use Mpdf\Mpdf;
use Cookie;

class PartnerinquiryController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $nrs = DB::table('pas_users')->where([["user_type", '=', User::USER_TYPE_MY_USER]])->count();
        $column_setting = ListingSetting::getPartnerInquiryDefaultListing();
        return view('partner-inquiry.index', compact('nrs','column_setting'));
    }
    
    public function add(){
       return view('partner-inquiry.inquiry-add');
    }
    
    public function submit(Request $request){
        $newarray = $this->isValid($request);
        if($newarray['status'] == 'success'){
            $idata['request_type'] = $request->request_type;
            $idata['request_reason'] = $request->request_reason;
            $idata['message'] = $request->message;  
            $idata['added_by'] = Auth::user()->id;
            $idata['added_date'] = Carbon::now()->format('Y-m-d H:i:s');
            $idata['status'] = 'Progress';
            $idata['partner_id'] = User::getPartnerDetail('id');;
            DB::table('pas_partner_inquiry')->insert($idata);
            $newarray = array("status"=>"success","msg"=>"Record added successfully.","lid"=>"");            
        }
        return response()->json($newarray);        
    }

    private function isValid($request){
        $newarray = array("status"=>"success");
        if($request->request_type == ''){
            $newarray = array("status"=>"fail", "msg"=>"Please select request type.");
        }elseif($request->request_reason == ''){
            $newarray = array("status"=>"fail","msg"=>"Please select request reason.");
        }elseif($request->message == ''){
            $newarray = array("status"=>"fail","msg"=>"Please enter message.");
        }
        return $newarray;
    }

    public function search(Request $request) {
        $records = $this->getSearchData($request);

        foreach ($records as $key => $record) {
            $records[$key]->id = pas_encrypt($record->id);
        }
        
        $column_setting = ListingSetting::getPartnerInquiryDefaultListing();
        return view('partner-inquiry._view', compact('records','column_setting'));
        //return response()->json($records);
    }
    


    public function exportexcel(Request $request) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Request Type');
        $sheet->setCellValue('B1', 'Request Date');
        $sheet->setCellValue('C1', 'Request Reason');
        $sheet->setCellValue('D1', 'Request Time');
        $sheet->setCellValue('E1', 'Request By');
        $sheet->setCellValue('F1', 'Request Message');
        $sheet->setCellValue('G1', 'Request Status');

        $result = $this->getSearchData($request);
        $rows = 2;
        foreach($result as $val){
            $sheet->setCellValue('A' . $rows, $val->request_type);
            $sheet->setCellValue('B' . $rows, date(Utility::DEFAULT_DATE_FORMAT,strtotime($val->added_date)));
            $sheet->setCellValue('C' . $rows, $val->request_reason);
            $sheet->setCellValue('D' . $rows, date(Utility::DEFAULT_TIME_FORMAT,strtotime($val->added_date)));
            $sheet->setCellValue('E' . $rows, $val->firstname.' '.$val->lastname);
            $sheet->setCellValue('F' . $rows, $val->message);
            $sheet->setCellValue('G' . $rows, $val->status);
            $rows++;
        }

        $filename = "partner_inquiry_lists.xlsx";
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

        $hd = public_path('images/logo.png');
        $wt = public_path('images/bg.png');
        $str = '';
        $str .= '<div style="border:2px solid #666; padding:10px; font-family: arial, sans-serif;">';
        $str .= '<div style="text-align:center"><img src="'.$hd.'" style="width:600px" alt=""/></div>';
        $str .= '<div style="position: relative;">';
        //$str .= '<div style="text-align:center;"><img src="'.$wt.'" style="width:500px" alt=""/></div>';
        $str .= '<div style="position:absolute;top:0px;width:100%">';
        $str .= '<h2 style="padding:10px; font-family: arial, sans-serif;font-size: 16px;">Partner Inquiry List</h2>';
        $str .= '<table style="width:100%;margin:0 auto;border-collapse: collapse;border:1px solid #333;">';
        $str .= '<tr>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:15%;font-weight:bold;text-align:left">Request Type</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Request Date</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:20%;font-weight:bold;text-align:left">Request Reason</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Request Time</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:15%;font-weight:bold;text-align:left">Request By</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:20%;font-weight:bold;text-align:left">Request Message</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;width:10%;border-bottom:1px solid #333;font-weight:bold;text-align:left">Request Status</th>';
        $str .= '</tr>';
        $result = $this->getSearchData($request);

        if(count($result) > 0){
        foreach($result as $val){
            $str .= '<tr>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->request_type.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.date(Utility::DEFAULT_DATE_FORMAT,strtotime($val->added_date)).'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->request_reason.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.date(Utility::DEFAULT_TIME_FORMAT,strtotime($val->added_date)).'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->firstname.' '.$val->lastname.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->message.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->status.'</td>';
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
        return $mpdf->Output("Partner_Inquiry_Lists.pdf", 'D');
    }

    /**
     * @param Request $request
     * @return mixed
     */
    private function getSearchData(Request $request){
        $query = DB::table('pas_partner_inquiry as p')->join('pas_users', function($join){
            $join->on('p.added_by', '=', 'pas_users.id');
        })
        ->select(['p.*', 'pas_users.firstname', 'pas_users.lastname']);

        return $this->addFilters($request, $query);
    }
    private function addFilters(Request $request, $query){
        $query->where('p.partner_id', '=', User::getPartnerDetail('id'));
        if (isset($request->q)){
            $query->where(function ($query) use ($request) {
                $query->where('p.request_type', 'like', '%'.$request->q.'%')
                    ->orwhere('p.request_reason', 'like', '%'.$request->q.'%')
                    ->orwhere('pas_users.firstname', 'like', '%'.$request->q.'%')
                    ->orwhere('pas_users.lastname', 'like', '%'.$request->q.'%')
                    ->orwhere('p.message', 'like', '%'.$request->q.'%');
                $query->orwhere(Db::raw('(DATE_FORMAT(p.added_date, "%m/%d/%Y %h:%m %p"))'), 'like', "%".$request->q."%");
            });
        }
        if (isset($request->request_type) && $request->request_type != ''){
            $query->where('p.request_type', 'like', '%'.$request->request_type.'%');
        }
        if (isset($request->request_reason) && $request->request_reason != ''){
            $query->where('p.request_reason', 'like', '%'.$request->request_reason.'%');
        }
        return $query->orderBy('id', 'DESC')->get();
    }
    public function delete(Request $request){
        $ids = request('id');
        $ids_arr = @explode(',', $ids);

        if(count($ids_arr) > 0){
           $ids_arr = array_filter(array_map('pas_decrypt', $ids_arr));

            $old_data = DB::table('pas_partner_inquiry')->whereIn("pas_partner_inquiry.id", $ids_arr)
                ->select(['pas_partner_inquiry.*', 'pas_users.firstname', 'pas_users.lastname'])
                ->leftJoin('pas_users', 'pas_users.id', '=', 'pas_partner_inquiry.added_by')
                ->get()->all();

            //dd($old_data);

            $leeds_data['action'] = 'delete';
            $leeds_data['old_data'] = json_encode($old_data);
            //$leeds_data['new_data'] = null;
            $leeds_data['ref_ids'] = implode(',', $ids_arr);
            UserActivityHelper::getInstance()->save($request, $leeds_data);

            DB::table('pas_partner_inquiry')->whereIn('id', $ids_arr)->delete();
        }
        $newarray = array("status"=>"success");
        return response()->json($newarray);
    }
}
