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
use App\Users;
use App\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use Config;
use Lang;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
require base_path("vendor/autoload.php");
use Mpdf\Mpdf;

class WeusersController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function main()
    {
        if(!UserAccess::hasAccess(UserAccess::WE_USERS_LIST_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $roles = Roles::where('role_type', '=', Roles::ROLE_TYPE_USER)->get();
        $column_setting = ListingSetting::getWeUsersDefaultListing();
        return view('dashboard.weusers', compact('roles','column_setting'));
    }

    public function new(){
        if(!UserAccess::hasAccess(UserAccess::WE_USERS_LIST_ACCESS, 'add')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $roles = Roles::where('role_type', '=', Roles::ROLE_TYPE_USER)->get();
        return view('dashboard.weusersentry', compact('roles'));
    }

    public function new_store(Request $request){
        if(!UserAccess::hasAccess(UserAccess::WE_USERS_LIST_ACCESS, 'add')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $id = pas_decrypt($request->id);
        $newarray = $this->isValid($request);
        if($newarray['status'] == 'success'){
            $idata['firstname'] = $request->firstname;
            $idata['lastname'] = $request->lastname;
            $idata['roleid'] = $request->role;
            $idata['email'] = $request->email;
            $idata['status'] = $request->status;
            $idata['phone'] = $request->phone;
            $idata['augusoft_campus'] = $request->augusoft_campus;
            $idata['user_type'] = User::USER_TYPE_WE_USER;

            $image = $request->photo;
            if($image != ''){
                $s3 = \Storage::disk('s3');
                $file_name = uniqid() .'.'. $image->getClientOriginalExtension();
                $s3filePath = '/partner/' . $file_name;
                $s3->put($s3filePath, file_get_contents($image), 'public');
                $idata['photo'] = $file_name;
            }else{
                $idata['photo'] = request('old_pic');
            }

            $idata['access_level'] = $request->role;
            if($id > 0){
                $nrs = DB::table('pas_users')->where([["email", '=', $request->email],['id', '!=', $id]])->count();
                if($nrs == 0){

                    $user_data = DB::table('pas_users')->select('firstname', 'lastname', 'status', 'email', 'last_wrong_attempted_at')
                        ->where("id", '=', $id)->first();

                    if(!empty($user_data->last_wrong_attempted_at) && $idata['status'] == 1){
                        $idata['last_wrong_attempted_at'] = null;
                        DB::table('pas_wrong_login')->where('user_id', $id)->delete();
                        $this->sendEmailAccountEnable($user_data);
                    }

                    $old_data = $this->getUser( [$id] );

                    $status = \App\Utility::getStatus();
                    if($status[$old_data->status]){
                        $old_data->status = $status[$old_data->status];
                    }

                    DB::table('pas_users')->where([["id", '=', $id]])->update($idata);

                    $leeds_data['action'] = 'update';
                    $leeds_data['old_data'] = json_encode($old_data);
                    $leeds_data['new_data'] = json_encode($idata);
                    $leeds_data['ref_ids'] = $id;
                    UserActivityHelper::getInstance()->save($request, $leeds_data);

                    $newarray = array("status"=>"success","msg"=>"Record updated successfully.","lid"=>"");
                }else{
                    $newarray = array("status"=>"fail","msg"=>"This email already exist.");
                }
            }else{
                $nrs = DB::table('pas_users')->where([["email", '=', $request->email]])->count();
                if($nrs == 0){
                    $rnd = Utility::generateStrongPassword();
                    $idata['password'] = md5($rnd);
                    DB::table('pas_users')->insert($idata);
                    $id = DB::getPdo()->lastInsertId();

                    $leeds_data['action'] = 'create';
                    //$leeds_data['old_data'] = json_encode($old_data);
                    $leeds_data['new_data'] = json_encode($idata);
                    $leeds_data['ref_ids'] = $id;
                    UserActivityHelper::getInstance()->save($request, $leeds_data);

                    $newarray = array("status"=>"success","msg"=>"Record added successfully.","lid"=>"");
                    if($id > 0){
                        $placeholder['FN'] = $request->firstname;
                        $placeholder['USERNAME'] = $request->email;
                        $placeholder['PASSWORD'] = $rnd;
                        $placeholder['URL'] = $_ENV['SITE_URL'];

                        $email_req = new EmailRequest();
                        $email_req->setTemplate(EmailTemplates::WE_USER_REGISTRATION)
                            ->setPlaceholder($placeholder)
                            ->setTo([[$request->email, $request->firstname]])
                            ->setLogSave(true);

                        $email_helper = new EmailHelper($email_req);
                        $email_helper->sendEmail();
                    }
                }else{
                    $newarray = array("status"=>"fail","msg"=>"This email already exist.");
                }
            }

        }
        return response()->json($newarray);
    }

    private function isValid($request){
        $newarray = array("status"=>"success");
        if($request->firstname == ''){
            $newarray = array("status"=>"fail", "msg"=>"Please enter first name.");
        }elseif($request->lastname == ''){
            $newarray = array("status"=>"fail","msg"=>"Please enter last name.");
        }elseif($request->role == ''){
            $newarray = array("status"=>"fail","msg"=>"Please select role.");
        }elseif($request->email == ''){
            $newarray = array("status"=>"fail","msg"=>"Please enter email.");
        }elseif(!filter_var($request->email, FILTER_VALIDATE_EMAIL)){
            $newarray = array("status"=>"fail","msg"=>"Please enter valid email.");
        }elseif($request->phone == ''){
            $newarray = array("status"=>"fail","msg"=>"Please enter phone.");
        }elseif($request->status == ''){
            $newarray = array("status"=>"fail","msg"=>"Please select status.");
        }
        return $newarray;
    }

    public function feature_ajax(Request $request) {
        if(!UserAccess::hasAccess(UserAccess::WE_USERS_LIST_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }

        $records = $this->getSearchData($request);

        foreach ($records as $key => $record) {
            $records[$key]->id = pas_encrypt($record->id);
            if(!empty($record->last_active)){
                $records[$key]->last_active = date(Utility::DEFAULT_DATE_TIME_FORMAT ,strtotime($record->last_active));
            }
        }
        
        $column_setting = ListingSetting::getWeUsersDefaultListing();
        return view('dashboard._weusers_view', compact('records','column_setting'));
        //return response()->json($records);
    }


    public function change(Request $request){
        if(!UserAccess::hasAccess(UserAccess::WE_USERS_LIST_ACCESS, 'add')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $id = pas_decrypt($request->id);
        if(empty($id)){
            return redirect(route('we-users'));
        }
        $edata = DB::table('pas_users')->where([["id", '=', pas_decrypt($request->id)]])->first();
        if(!$edata){
            return redirect(route('we-users'));
        }

        $roles = Roles::where('role_type', '=', Roles::ROLE_TYPE_USER)->get();

        return view('dashboard.weusersedit', compact('edata', 'roles'));
    }

    public function remove(Request $request){
        if(!UserAccess::hasAccess(UserAccess::WE_USERS_LIST_ACCESS, 'delete')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $ids = request('id');
        $ids_arr = @explode(',', $ids);

        $skipped_record_errors = [];
        //$ids_arr1 = [];

        if(count($ids_arr) > 0){
            $ids_arr = array_filter(array_map('pas_decrypt', $ids_arr));
            //print"<pre>";print_r($ids_arr);die;
            /*if(count($ids_arr) > 0){
                $i=1;
                foreach($ids_arr as $ids){
                    $nrs = DB::table('pas_student')->where('created_by','=',$ids)->count('id');
                    if($nrs == 0){
                        $ids_arr1[] = $ids;
                    }else{
                        $skipdata = DB::table('pas_users')->select("firstname","lastname")->where('id', '=', $ids)->get()->first();
                        $skipped_record_errors[] = 'Records created by '.$skipdata->firstname.' '.$skipdata->lastname.' is already exits in student enrollment.';                        
                    }
                    $i++;
                }
            }*/
            if(count($ids_arr) > 0){

                $old_data = DB::table('pas_users')->whereIn("pas_users.id", $ids_arr)
                            ->select(['pas_users.id','pas_users.user_type', 'pas_users.email', 'pas_users.password', 'pas_users.firstname', 'pas_users.lastname', 'pas_users.last_active', 'pas_users.status', 'pas_users.request_time', 'pas_users.reset_status'
                            , 'pas_users.first_login', 'pas_users.login_status', 'pas_users.otp', 'pas_users.photo', 'pas_users.phone', 'pas_users.added_by', 'pas_users.login_code', 'pas_users.last_wrong_attempted_at', 'pas_users.highlight_reports', 'pas_users.password_expired_at', 'pas_roles.role_name'])
                            ->leftJoin('pas_roles', 'pas_roles.id', '=', 'pas_users.roleid')
                            ->get()->all();

                $leeds_data['action'] = 'delete';
                $leeds_data['old_data'] = json_encode($old_data);
                //$leeds_data['new_data'] = null;
                $leeds_data['ref_ids'] = implode(',', $ids_arr);
                
                DB::table('pas_student')->whereIn('created_by', $ids_arr)->delete();
                DB::table('password_history')->whereIn('user_id', $ids_arr)->delete();
                $results = DB::table('pas_users')->whereIn('id', $ids_arr)->delete();
                if($results){
                    UserActivityHelper::getInstance()->save($request, $leeds_data);
                }
            }
        }
        return response()->json([
            'status' => 'success',
            'success' => count($ids_arr),
            'skipped' => count($skipped_record_errors),
            'error' => $skipped_record_errors
        ]);
    }


    public function excel_export(Request $request) {
        if(!UserAccess::hasAccess(UserAccess::WE_USERS_LIST_ACCESS, 'download')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'First Name');
        $sheet->setCellValue('B1', 'Last Name');
        $sheet->setCellValue('C1', 'Email');
        $sheet->setCellValue('D1', 'Phone');
        $sheet->setCellValue('E1', 'Role');
        $sheet->setCellValue('F1', 'Status');
        $sheet->setCellValue('G1', 'Last Login');

        $result = $this->getSearchData($request);

        $rows = 2;
        foreach($result as $val){
            $sheet->setCellValue('A' . $rows, $val->firstname);
            $sheet->setCellValue('B' . $rows, $val->lastname);
            $sheet->setCellValue('C' . $rows, $val->email);
            $sheet->setCellValue('D' . $rows, $val->phone);
            $sheet->setCellValue('E' . $rows, $val->role_name);
            $sheet->setCellValue('F' . $rows, $val->status);
            $sheet->setCellValue('G' . $rows, (!empty($val->last_active) ? date(Utility::DEFAULT_DATE_TIME_FORMAT, strtotime($val->last_active)): ''));
            $rows++;
        }
        $filename = "weusers_lists.xlsx";
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

    public function pdf_export(Request $request){
        if(!UserAccess::hasAccess(UserAccess::WE_USERS_LIST_ACCESS, 'download')){
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
        $str .= '<h2 style="padding:10px; font-family: arial, sans-serif;font-size: 16px;">WE Users List</h2>';
        $str .= '<table style="width:100%;margin:0 auto;border-collapse: collapse;border:1px solid #333;">';
        $str .= '<tr>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">First Name</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Last Name</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Email</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Phone</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:15%;font-weight:bold;text-align:left">Role</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:15%;font-weight:bold;text-align:left">Status</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Last Login</th>';
        $str .= '</tr>';

        if(count($result) > 0){
        foreach($result as $val){
            $str .= '<tr>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->firstname.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->lastname.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->email.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->phone.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->role_name.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->status.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.(!empty($val->last_active) ? date(Utility::DEFAULT_DATE_TIME_FORMAT, strtotime($val->last_active)): '').'</td>';
            $str .= '</tr>';

        }
        }else{
            $str .= '<tr>';
            $str .= '<td colspan="6" style="text-align:center;">No Record Found.</td>';
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
        return $mpdf->Output("WE_Users_Lists.pdf", 'D');
    }

    private function getSearchData(Request $request){
        $query = DB::table('pas_users as u')
            ->join('pas_roles', function($join){
                $join->on('u.roleid', '=', 'pas_roles.id');
            });

        $query->where('u.user_type', '=', '3')
            ->select('u.id', 'u.firstname', 'u.lastname', 'u.email', 'u.phone', 'pas_roles.role_name', DB::raw('IF(u.status = 1, "Active", "Locked") as status'), 'last_active');
        if (isset($request->q)){
            $query->Where(function ($query) use ($request) {
                $sts = '';
                if(strtolower($request->q) == 'active'){
                    $sts = '1';
                }elseif(strtolower($request->q) == 'locked'){
                    $sts = '2';
                }
                $query->where('lastname', 'like', '%'.$request->q.'%')
                    ->orwhere('email', 'like', '%'.$request->q.'%')
                    ->orwhere('firstname', 'like', '%'.$request->q.'%')
                    ->orwhere('email', 'like', '%'.$request->q.'%')
                    ->orwhere('phone', 'like', '%'.$request->q.'%')
                    ->orwhere('pas_roles.role_name', 'like', '%'.$request->q.'%')
                    ->orwhere('u.status', '=', $sts)
                    ->orwhere(Db::raw('(DATE_FORMAT(u.last_active, "%m/%d/%Y %h:%m %p"))'), 'like', "%".$request->q."%");
            });
        }
        if (isset($request->fname)){
            $query->where('firstname', 'like', '%'.$request->fname.'%');
        }
        if (isset($request->lname)){
            $query->where('lastname', 'like', '%'.$request->lname.'%');
        }
        if (isset($request->role)){
            $query->where('roleid', '=', $request->role);
        }
        if (isset($request->status)){
            $query->where('u.status', '=', $request->status);
        }
        if (isset($request->email)){
            $query->where('email', 'like', '%'.$request->email.'%');
        }
        if (isset($request->phone)){
            $query->where('phone', 'like', '%'.$request->phone.'%');
        }

        $query->orderByDesc('u.id');
        return $query->get();
    }

    private function sendEmailAccountEnable($user_data){
        $placeholder['FN'] = $user_data->firstname;
        $placeholder['USERNAME'] = $user_data->email;
        $placeholder['URL'] = $_ENV['SITE_URL'];

        $email_req = new EmailRequest();
        $email_req->setTemplate(EmailTemplates::ENABLE_ACCOUNT_WRONG_PASSWORD)
            ->setPlaceholder($placeholder)
            ->setTo([[$user_data->email, $user_data->firstname]])
            ->setLogSave(true);

        $email_helper = new EmailHelper($email_req);
        $email_helper->sendEmail();
    }

    /**
     * @param $ids
     */
    private function getUser($ids)
    { 
        $old_data = DB::table('pas_users')->whereIn("pas_users.id", $ids)
            ->select(['pas_users.id','pas_users.email', 'pas_users.firstname', 'pas_users.lastname', 'pas_users.photo', 'pas_users.phone', 'pas_users.status', 'pas_roles.role_name'])
            ->leftJoin('pas_roles', 'pas_roles.id', '=', 'pas_users.roleid')
            ->get()->first();
        return $old_data;
    }

}
