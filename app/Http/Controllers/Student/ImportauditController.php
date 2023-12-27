<?php
namespace App\Http\Controllers\Student;
use App\Http\Controllers\Controller;
use App\Models\Roles;
use App\Models\Country;
use App\Models\User;
use App\Models\UserAccess;
use App\Utility;
use App\Models\ListingSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Excel;
use Session;
use Config;
use Lang;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
require base_path("vendor/autoload.php");
use Mpdf\Mpdf;
use Cookie;

class ImportauditController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if(!UserAccess::hasAccess(UserAccess::STUDENT_IMPORT_AUDIT_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $roles = Roles::where('role_type', '=', Roles::ROLE_TYPE_PARTNER)->get();
        $column_setting = ListingSetting::getStudentImportDefaultListing();
        //print"<pre>";print_r($column_setting);die;
        return view('student.importaudit', compact('roles','column_setting'));
    }
    
    public function ajax(Request $request) {   
        $result = $this->getSearchData($request);   
        $data = array();
        foreach ($result as $val) { 
            $url =  'uploads/'.$val->file;
            $data[] = array(
                "added_by"=>$val->fullname,
                "id"=>$val->id,
                "mid"=>pas_encrypt($val->id),
                "date"=>date(Utility::DEFAULT_DATE_FORMAT, strtotime($val->date)),
                "processing_time"=> $val->processing_time,
                "imported_records"=>$val->records_imported,
                "records_imported_warning"=>$val->records_imported_warning,
                "records_skiped"=>$val->records_skiped,
                "file_size"=>$val->file_size,
                "file"=>$val->file,
                "url"=>$url,
                "import_time"=>date(Utility::DEFAULT_TIME_FORMAT, strtotime($val->added_date)));
        }
        $column_setting = ListingSetting::getStudentImportDefaultListing();
        //print"<pre>";print_r($data);die;
        return view('student._import_view', compact('data','column_setting'));
    }
    
    public function exportexcel(Request $request) {
        if(!UserAccess::hasAccess(UserAccess::STUDENT_IMPORT_AUDIT_ACCESS, 'download')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'File Name');
        $sheet->setCellValue('B1', 'Import Date');
        $sheet->setCellValue('C1', 'Import Time');
        $sheet->setCellValue('D1', 'File Size');
        $sheet->setCellValue('E1', 'Processing Time');
        $sheet->setCellValue('F1', 'Imported Records');
        $sheet->setCellValue('G1', 'Imported Warning');
        $sheet->setCellValue('H1', 'Skipped Records');
        $sheet->setCellValue('I1', 'Imported By');
        $result = $this->getSearchData($request);    
        $rows = 2;//print"<pre>";print_r($result);die;
        if(count($result) > 0){
            foreach($result as $val){
                /*$fsize = number_format($val->file_size / 1024,2);
                if($val->processing_time <= 0) $pt = "< 1 sec"; else $pt = $val->processing_time.' sec';*/
                $sheet->setCellValue('A' . $rows, $val->file);
                $sheet->setCellValue('B' . $rows, date(Utility::DEFAULT_DATE_FORMAT, strtotime($val->date)));
                $sheet->setCellValue('C' . $rows, date(Utility::DEFAULT_TIME_FORMAT, strtotime($val->added_date)));
                $sheet->setCellValue('D' . $rows, $val->file_size.'KB');
                $sheet->setCellValue('E' . $rows, $val->processing_time);
                $sheet->setCellValue('F' . $rows, $val->records_imported);
                $sheet->setCellValue('G' . $rows, $val->records_imported_warning);
                $sheet->setCellValue('H' . $rows, $val->records_skiped);
                $sheet->setCellValue('I' . $rows, $val->fullname);
                $rows++;
            }
        }

        $filename = "importaudit_list.xlsx";
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
        /*return response()->download("export/".$filename, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);*/
    }
    
    public function exportpdf(Request $request){
        if(!UserAccess::hasAccess(UserAccess::STUDENT_IMPORT_AUDIT_ACCESS, 'download')){
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
        $str .= '<h2 style="padding:10px; font-family: arial, sans-serif;font-size: 16px;">Student Import Audit</h2>';
        $str .= '<table style="width:100%;margin:0 auto;border-collapse: collapse;border:1px solid #333;">';
        $str .= '<tr>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:20%;font-weight:bold;text-align:left">File Name</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Import Date</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Import Time</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">File Size</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Processing Time</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Imported Records</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Imported Warning</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Skipped Records</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-bottom:1px solid #333;width:20%;font-weight:bold;text-align:left">Imported By</th>';
        $str .= '</tr>';
        
        if(count($result) > 0){
        foreach($result as $val){
            //$fsize = number_format($val->file_size / 1024,2);
            //if($val->processing_time <= 0) $pt = "< 1 sec"; else $pt = $val->processing_time.' sec';
            $str .= '<tr>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->file.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.date(Utility::DEFAULT_DATE_FORMAT, strtotime($val->date)).'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.date(Utility::DEFAULT_TIME_FORMAT, strtotime($val->added_date)).'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->file_size.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->processing_time.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->records_imported.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->records_imported_warning.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->records_skiped.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-bottom:1px solid #333;">'.$val->fullname.'</td>';
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
        return $mpdf->Output("Student_Import_Audit.pdf", 'D');
    } 
    
    private function getSearchData(Request $request){
        ///dd(User::getPartnerDetail('id'));
        //dd(DB::getQueryLog());die; // last query
        $query = DB::table('pas_imported_files AS r')->select('r.id', 'file', 'r.date', 'records_imported', DB::raw('CONCAT(firstname, " ", lastname) AS fullname'), 'records_skiped', DB::raw('CONCAT(ROUND((file_size / 1024), 2), "", "KB") AS file_size'), DB::raw('CONCAT(processing_time, " ", "sec") AS processing_time'),'added_date', 'records_imported_warning')
            ->leftJoin('pas_users', function($join){
                $join->on('r.added_by', '=', 'pas_users.id');
            })
            ->where('r.partner_id', '=', User::getPartnerDetail('id'));

        if (isset($request->q)){
            $query->where(function ($query) use ($request) {
                $query->orwhere('file', 'like', '%'.$request->q.'%')
                    ->orwhere('r.date', 'like', '%'.$request->q.'%')
                    ->orwhere('records_imported', 'like', '%'.$request->q.'%')
                    ->orwhere('records_skiped', 'like', '%'.$request->q.'%')
                    ->orwhere('records_imported', 'like', '%'.$request->q.'%')
                    ->orwhere(DB::raw('CONCAT(firstname, " ", lastname)'), 'like', '%'.$request->q.'%')
                    ->orwhere(DB::raw('CONCAT(processing_time, " ", "sec")'), 'like', '%'.$request->q.'%')
                    ->orwhere(DB::raw('CONCAT(ROUND((file_size / 1024), 2), "", "KB")'), 'like', '%'.$request->q.'%')
                    ->orwhere(Db::raw('(DATE_FORMAT(added_date, "%h:%i %p"))'), 'like', "%".$request->q."%");
            });
        }    
        $query->orderBy('id','DESC');
        return $query->get();
        //dd(DB::getQueryLog());die; // last query
    }
    
    public function delete(){
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
    }
    

}
