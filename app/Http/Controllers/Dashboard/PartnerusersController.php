<?php
namespace App\Http\Controllers\Dashboard;
use App\EmailHelper;
use App\EmailRequest;
use App\Http\Controllers\Controller;
use App\Models\EmailTemplates;
use App\Models\Partner;
use App\Models\PartnerType;
use App\Models\Roles;
use App\Models\User;
use App\Models\UserAccess;
use App\Models\ListingSetting;
use App\UserActivityHelper;
use App\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
require base_path("vendor/autoload.php");
use Mpdf\Mpdf;
use Session;
use Config;
use Lang;
use Cookie;

class PartnerusersController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function main()
    {
        if(!UserAccess::hasAccess(UserAccess::PARTNER_USERS_LIST_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $roles = Roles::where('role_type', '=', Roles::ROLE_TYPE_PARTNER)->get();
        $partner_types = PartnerType::where('status', '=', Utility::STATUS_ACTIVE)->get();
        $partners = Partner::where('status', '=', Utility::STATUS_ACTIVE)->get();
        $column_setting = ListingSetting::getPartnerUsersDefaultListing();
        return view('dashboard.partnerusers', compact('partner_types', 'roles', 'partners', 'column_setting'));
    }

    public function insert(){
        if(!UserAccess::hasAccess(UserAccess::PARTNER_USERS_LIST_ACCESS, 'add')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $roles = Roles::where('role_type', '=', Roles::ROLE_TYPE_PARTNER)->get();
        $partner_types = PartnerType::where('status', '=', Utility::STATUS_ACTIVE)->get();
        $partners = Partner::where('partner_type', '=', 'Active')->get();

        $opt = '';
        if(count($partners) > 0){
            $opt .= '<option value="">Please Select</option>';
            foreach($partners as $partner){
                $opt .= '<option value="'.$partner->id.'" data-parent="'.$partner->parent_partner_name.'">'.$partner->partner_name.'</option>';
            }
        }

       return view('dashboard.partneruserentry', compact('partner_types', 'roles', 'opt', 'partners'));
    }

    public function store_insert(Request $request){
        if(!UserAccess::hasAccess(UserAccess::PARTNER_USERS_LIST_ACCESS, 'add')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $id = pas_decrypt($request->id);
        $idata = array();
        $firstname = request('firstname');
        $idata['firstname'] = $firstname;
        $lastname = request('lastname');
        $idata['lastname'] = $lastname;
        $role = request('role');
        $idata['roleid'] = $role;
        $email = request('email');
        $idata['email'] = $email;
        $status = request('status');
        $idata['status'] = $status;
        $partner_id = request('partner_id');
        $idata['partner_id'] = $partner_id;
        $partner_type = request('partner_type');
        $idata['partner_type'] = $partner_type;
        $phone = request('phone');
        $idata['phone'] = $phone;
        $augusoft_campus = request('augusoft_campus');
        $idata['augusoft_campus'] = $augusoft_campus;
        $idata['user_type'] = User::USER_TYPE_PARTNER;
        if($firstname == ''){
                $newarray = array("status"=>"fail", "msg"=>"Please enter first name.");
        }elseif($lastname == ''){
                $newarray = array("status"=>"fail","msg"=>"Please enter last name.");
        }elseif($role == ''){
                $newarray = array("status"=>"fail","msg"=>"Please select role.");
        }elseif($email == ''){
                $newarray = array("status"=>"fail","msg"=>"Please enter email.");
        }elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                $newarray = array("status"=>"fail","msg"=>"Please enter valid email.");
        }elseif($phone == ''){
                $newarray = array("status"=>"fail","msg"=>"Please enter phone.");
        }elseif($status == ''){
                $newarray = array("status"=>"fail","msg"=>"Please select status.");
        }elseif($partner_id == ''){
                $newarray = array("status"=>"fail","msg"=>"Please select partner.");
        }elseif($partner_type == ''){
                $newarray = array("status"=>"fail","msg"=>"Please select partner type.");
        }else{
            # get file from request object
            $image = request('photo');
            if($image != ''){
                $s3 = \Storage::disk('s3');
                $file_name = uniqid() .'.'. $image->getClientOriginalExtension();
                $s3filePath = '/partner/' . $file_name;
                $s3->put($s3filePath, file_get_contents($image), 'public');
                $idata['photo'] = $file_name;
            }else{
                $idata['photo'] = request('old_pic');
            }

            if($role == User::ROLE_REGISTRATION_ACCOUNT){
                $idata['access_level'] = User::ACCESS_LEVEL_REGISTRATION_ACCOUNT_PARTNER;
            }elseif($role == User::ROLE_ACCOUNT_MANAGER){
                $idata['access_level'] = User::ACCESS_LEVEL_ACCOUNT_MANAGER;
            }elseif($role == User::ROLE_ACCOUNT_SUPPORT){
                $idata['access_level'] = User::ACCESS_LEVEL_ACCOUNT_SUPPORT;
            }

            if($id > 0){
                $nrs = DB::table('pas_users')->where([["email", '=', $email],['id', '!=', $id]])->count();
                if($nrs == 0){
                    $user_data = DB::table('pas_users')->select('firstname', 'lastname', 'status', 'email', 'last_wrong_attempted_at')
                        ->where("id", '=', $id)->first();

                    if(request('old_role') != $role && isset($idata['access_level']) && !empty($idata['access_level'])){
                        $access_level = DB::table('pas_roles_access')->where([
                            ['access_level', '=', $idata['access_level']],
                            ['role_id', '=', $role],
                        ])->get()->all();
                        $access_level_data = [];
                        foreach ($access_level as $key => $item) {
                            if($item->can_view == 1){
                                $access_level_data[$key]['user_id'] = $id;
                                $access_level_data[$key]['feature'] = $item->feature;
                                $access_level_data[$key]['parent_menu'] = $item->parent_menu;
                                $access_level_data[$key]['can_view'] = $item->can_view;
                                $access_level_data[$key]['can_download'] = $item->can_download;
                                $access_level_data[$key]['can_add'] = $item->can_add;
                            }
                        }
                        DB::table('pas_users_access')->where('user_id', '=', $id)->delete();
                        DB::table('pas_users_access')->insert($access_level_data);
                    }

                    if(!empty($user_data->last_wrong_attempted_at) && $idata['status'] == 1){
                        $idata['last_wrong_attempted_at'] = null;

                        DB::table('pas_wrong_login')->where('user_id', $id)->delete();

                        $this->sendEmailAccountEnable($user_data);
                    }
                    $old_data = DB::table('pas_users')->where([["pas_users.id", '=', $id]])->select(['pas_users.email', 'pas_users.firstname', 'pas_users.lastname', 'pas_users.photo', 'pas_users.phone', 'pas_users.partner_type', 'pas_users.status', 'pas_roles.role_name', 'pas_partner_type.partner_type', 'pas_partner.partner_name'])
                        ->leftJoin('pas_roles', 'pas_roles.id', '=', 'pas_users.roleid')
                        ->leftJoin('pas_partner_type', 'pas_partner_type.id', '=', 'pas_users.partner_type')
                        ->leftJoin('pas_partner', 'pas_partner.id', '=', 'pas_users.partner_id')
                        ->get()->first();

                    $status = \App\Utility::getStatus();
                    if($status[$old_data->status]){
                        $old_data->status = $status[$old_data->status];
                    }
                    //dd($old_data);
                    DB::table('pas_users')->where([["pas_users.id", '=', $id]])->update($idata);

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
                $nrs = DB::table('pas_users')->where([["email", '=', $email]])->count();
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

                        if(request('old_role') != $role && isset($idata['access_level']) && !empty($idata['access_level'])){
                            $access_level = DB::table('pas_roles_access')->where([
                                ['access_level', '=', $idata['access_level']],
                                ['role_id', '=', $role],
                            ])->get()->all();
                            $access_level_data = [];
                            foreach ($access_level as $key => $item) {
                                if($item->can_view == 1){
                                    $access_level_data[$key]['user_id'] = $id;
                                    $access_level_data[$key]['feature'] = $item->feature;
                                    $access_level_data[$key]['parent_menu'] = $item->parent_menu;
                                    $access_level_data[$key]['can_view'] = $item->can_view;
                                    $access_level_data[$key]['can_download'] = $item->can_download;
                                    $access_level_data[$key]['can_add'] = $item->can_add;
                                }
                            }
                            DB::table('pas_users_access')->insert($access_level_data);
                        }

                        // to send email
                        $placeholder['FN'] = $firstname;
                        $placeholder['USERNAME'] = $email;
                        $placeholder['PASSWORD'] = $rnd;
                        $placeholder['URL'] = $_ENV['SITE_URL'];

                        $email_req = new EmailRequest();
                        $email_req->setTemplate(EmailTemplates::PARTNER_REGISTRATION)
                            ->setPlaceholder($placeholder)
                            /*->setFromName($_ENV['FROM_NAME'])
                            ->setFromEmail($_ENV['FROM_EMAIL'])*/
                            ->setTo([[$email, $firstname]])
                            //->setTo([['rajneesh@xoomwebdevelopment.com', 'Rajneesh']])
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

    public function feature_ajax(Request $request) {
        if(!UserAccess::hasAccess(UserAccess::PARTNER_USERS_LIST_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
            $records = $this->getSearchData($request);

            foreach ($records as $key => $record) {
                $records[$key]->id = pas_encrypt($record->id);
                if(!empty($record->last_active)){
                    $records[$key]->last_active = date(Utility::DEFAULT_DATE_TIME_FORMAT ,strtotime($record->last_active));
                }
            }
            $column_setting = ListingSetting::getPartnerUsersDefaultListing();
            return view('dashboard._partner_view', compact('records','column_setting'));
            //return response()->json($records);
    }


    public function change(Request $request){
        if(!UserAccess::hasAccess(UserAccess::PARTNER_USERS_LIST_ACCESS, 'add')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $id = pas_decrypt($request->id);
        if(empty($id)){
           return redirect(route('partner-users'));
        }
        $edata = DB::table('pas_users')->select(['pas_users.*', 'pas_partner.parent_partner_name'])
            ->leftJoin('pas_partner', function($join){
            $join->on('partner_id', '=', 'pas_partner.id');
        })->where([["pas_users.id", '=', pas_decrypt($request->id)]])->first();
        if(!$edata){
            return redirect(route('partner-users'));
        }

        $partners = Partner::where('partner_type', '=', 'Active')->get();

        $opt = '';
        if(count($partners) > 0){
            foreach($partners as $partner){
                $opt .= '<option value="'.$partner->id.'" data-parent="'.$partner->parent_partner_name.'" '.($edata->partner_id == $partner->id ? 'selected':'').'>'.$partner->partner_name.'</option>';
            }
        }

        $roles = Roles::where('role_type', '=', Roles::ROLE_TYPE_PARTNER)->get();
        $partner_types = PartnerType::where('status', '=', Utility::STATUS_ACTIVE)->get();

        return view('dashboard.partneruseredit', compact('partners', 'partner_types', 'roles','edata', 'opt'));
    }

    public function remove(Request $request){
        if(!UserAccess::hasAccess(UserAccess::PARTNER_USERS_LIST_ACCESS, 'delete')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $ids = request('id');
            $ids_arr = @explode(',', $ids);

        if(count($ids_arr) > 0){
           $ids_arr = array_filter(array_map('pas_decrypt', $ids_arr));
           if(count($ids_arr) > 0){
                $i=1;
                $skipped_record_errors = [];
                $ids_arr1 = [];
                foreach($ids_arr as $ids){
                    $nrs = DB::table('pas_student')->where('created_by','=',$ids)->count('id');
                    if($nrs == 0){
                        $ids_arr1[] = $ids;
                    }else{
                        $skipdata = DB::table('pas_users')->select("firstname","lastname")->where('id', '=', $ids)->get()->first();
                        $skipped_record_errors[] = 'Records created by '.$skipdata->firstname.' '.$skipdata->lastname.' is already exits in student enrollment.';                        
                    }
                $i++;}
            }
           if(count($ids_arr1) > 0){
                $old_data = DB::table('pas_users')->whereIn("pas_users.id", $ids_arr1)
                    ->select(['pas_users.id', 'pas_users.user_type', 'pas_users.email', 'pas_users.password', 'pas_users.firstname', 'pas_users.lastname', 'pas_users.last_active', 'pas_users.status', 'pas_users.request_time', 'pas_users.reset_status', 'pas_users.first_login', 'pas_users.login_status', 'pas_users.otp', 'pas_users.photo', 'pas_users.phone', 'pas_users.augusoft_campus', 'pas_users.access_level'
                    , 'pas_users.access_feature', 'pas_users.added_by', 'pas_users.login_code', 'pas_users.last_wrong_attempted_at', 'pas_users.highlight_reports', 'pas_users.password_expired_at', 'pas_roles.role_name', 'pas_partner_type.partner_type', 'pas_partner.partner_name'])
                    ->leftJoin('pas_roles', 'pas_roles.id', '=', 'pas_users.roleid')
                    ->leftJoin('pas_partner_type', 'pas_partner_type.id', '=', 'pas_users.partner_type')
                    ->leftJoin('pas_partner', 'pas_partner.id', '=', 'pas_users.partner_id')
                    ->get()->all();

                //dd($old_data);

                $leeds_data['action'] = 'delete';
                $leeds_data['old_data'] = json_encode($old_data);
                //$leeds_data['new_data'] = null;
                $leeds_data['ref_ids'] = implode(',', $ids_arr1);
                
                DB::table('password_history')->whereIn('user_id', $ids_arr1)->delete();
                $results = DB::table('pas_users')->whereIn('id', $ids_arr1)->delete();
                if($results){
                    UserActivityHelper::getInstance()->save($request, $leeds_data);
                }
            }
        }
        return response()->json([
            'status' => 'success',
            'success' => count($ids_arr1),
            'skipped' => count($skipped_record_errors),
            'error' => $skipped_record_errors
        ]);
    }


    public function excel_export(Request $request) {
        if(!UserAccess::hasAccess(UserAccess::PARTNER_USERS_LIST_ACCESS, 'download')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'First Name');
        $sheet->setCellValue('B1', 'Last Name');
        $sheet->setCellValue('C1', 'Role');
        $sheet->setCellValue('D1', 'Status');
        $sheet->setCellValue('E1', 'Email');
        $sheet->setCellValue('F1', 'Partner / Institution');
        $sheet->setCellValue('G1', 'Phone');
        $sheet->setCellValue('H1', 'Partner Type');
        $sheet->setCellValue('I1', 'Last Login');

        $result = $this->getSearchData($request);
        $rows = 2;
        foreach($result as $val){
            $sheet->setCellValue('A' . $rows, $val->firstname);
            $sheet->setCellValue('B' . $rows, $val->lastname);
            $sheet->setCellValue('C' . $rows, $val->role_name);
            $sheet->setCellValue('D' . $rows, $val->status);
            $sheet->setCellValue('E' . $rows, $val->email);
            $sheet->setCellValue('F' . $rows, $val->partner);
            $sheet->setCellValue('G' . $rows, $val->phone);
            $sheet->setCellValue('H' . $rows, $val->partner_type);
            $sheet->setCellValue('I' . $rows, (!empty($val->last_active) ? date(Utility::DEFAULT_DATE_TIME_FORMAT, strtotime($val->last_active)): ''));
            $rows++;
        }

        $filename = "partner_lists.xlsx";
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
        if(!UserAccess::hasAccess(UserAccess::PARTNER_USERS_LIST_ACCESS, 'download')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        
        $hd = public_path('images/logo.png');
        $wt = public_path('images/bg.png');
        $str = '';
        $str .= '<div style="border:2px solid #666; padding:10px; font-family: arial, sans-serif;">';
        $str .= '<div style="text-align:center"><img src="'.$hd.'" style="width:600px" alt=""/></div>';
        $str .= '<div style="position: relative;">';
        //$str .= '<div style="text-align:center;"><img src="'.$wt.'" style="width:500px" alt=""/></div>';
        $str .= '<div style="position:absolute;top:0px;width:100%">';
        $str .= '<h2 style="padding:10px; font-family: arial, sans-serif;font-size: 16px;">Partner Users List</h2>';
        $str .= '<table style="width:100%;margin:0 auto;border-collapse: collapse;border:1px solid #333;">';
        $str .= '<tr>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">First Name</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Last Name</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Role</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Status</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:15%;font-weight:bold;text-align:left">Email</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Partner / Institutio</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Phone</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:10%;font-weight:bold;text-align:left">Partner Type</th>';
        $str .= '<th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-bottom:1px solid #333;width:15%;font-weight:bold;text-align:left">Last Login</th>';
        $str .= '</tr>';
        $result = $this->getSearchData($request);

        if(count($result) > 0){
        foreach($result as $val){
            $str .= '<tr>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->firstname.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->lastname.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->role_name.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->status.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->email.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->partner.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->phone.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">'.$val->partner_type.'</td>';
            $str .= '<td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-bottom:1px solid #333;">'.(!empty($val->last_active) ? date(Utility::DEFAULT_DATE_TIME_FORMAT, strtotime($val->last_active)): '').'</td>';
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
        return $mpdf->Output("Partner_Users_Lists.pdf", 'D');
    }
    // partner permission action
    public function permit(Request $request) {
        if(!UserAccess::hasAccess(UserAccess::PARTNER_USERS_LIST_ACCESS, 'add')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $id = pas_decrypt($request->id);
        if(empty($id)){
            return redirect(route('partner-users'));
        }

        $roles = Roles::where('role_type', '=', Roles::ROLE_TYPE_PARTNER)->get();
        $edata = DB::table('pas_users')->select()->where([["id", '=', $id]])->first();
        return view('dashboard.partnerpermission', compact('edata', 'roles'));
    }

    public function get_access(Request $request){
        if(!UserAccess::hasAccess(UserAccess::PARTNER_USERS_LIST_ACCESS, 'add')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $all_user_features = UserAccess::getUserAccessFeatures(pas_decrypt($request->uid), $request->ut, $request->ur, $request->al);
        return view('dashboard.fetchaccess', compact( 'all_user_features'));
    }

    public function permit_store(Request $request){
        if(!UserAccess::hasAccess(UserAccess::PARTNER_USERS_LIST_ACCESS, 'add')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }

        if(empty($request->access_level)){
            $newarray = array("status"=>"fail", "msg"=>"Please select access level.");
        }else{
            $post = $request->all();
            //dd($post);
            $data = [];
            foreach ($post['feature'] as $key => $feature) {
                if(isset($feature['feature'])){
                    $data[$key]['user_id'] = $post['ids'];
                    $data[$key]['feature'] = $feature['feature'];
                    $data[$key]['parent_menu'] = $feature['parent_menu'];
                    $data[$key]['can_view'] = 1;
                    $data[$key]['can_download']  = (isset($feature['opt']) && isset($feature['opt']['download'])) ? $feature['opt']['download']:null;
                    $data[$key]['can_add'] = (isset($feature['opt']) && isset($feature['opt']['add'])) ? $feature['opt']['add']:null;
                }
            }
            //dd($data);
            if(count($data) > 0) {
                DB::table('pas_users_access')->where('user_id', $request->ids)->delete();
                DB::table('pas_users_access')->insert($data);
                $newarray = array("status"=>"success","msg"=>"Record updated successfully.","lid"=>"");
            }else{
                $newarray = array("status"=>"fail", "msg"=>"Something went wrong","lid"=>"");
            }
        }
        return response()->json($newarray);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    private function getSearchData(Request $request){
        $query = DB::table('pas_users as u')->join('pas_roles', function($join){
            $join->on('u.roleid', '=', 'pas_roles.id');
        })->join('pas_partner_type', function($join){
            $join->on('u.partner_type', '=', 'pas_partner_type.id');
        })->join('pas_partner', function($join){
            $join->on('u.partner_id', '=', 'pas_partner.id', null, 'left');
        })
            ->select('u.id', 'u.firstname', 'u.lastname', 'u.email', 'u.phone', 'u.partner', 'role_name', 'pas_partner_type.partner_type', 'partner_name AS partner', DB::raw('IF(u.status = 1, "Active", "Locked") as status'), 'last_active');
        $query->where('u.user_type', '=', User::USER_TYPE_PARTNER);
        return $this->addFilters($request, $query);
    }

    /**
     * @param Request $request
     * @param $query
     * @return mixed
     */
    private function addFilters(Request $request, $query){
        if (isset($request->q)){
            $query->where(function ($query) use ($request) {
                $sts = '';
                if(strtolower($request->q) == 'active'){
                    $sts = '1';
                }elseif (strtolower($request->q) == 'locked'){
                    $sts = '2';
                }

                $query->where('u.lastname', 'like', '%'.$request->q.'%')
                    ->orwhere('u.email', 'like', '%'.$request->q.'%')
                    ->orwhere('u.firstname', 'like', '%'.$request->q.'%')
                    ->orwhere('pas_partner.partner_name', 'like', '%'.$request->q.'%')
                    ->orwhere('pas_roles.role_name', 'like', '%'.$request->q.'%')
                    ->orwhere('pas_partner_type.partner_type', 'like', '%'.$request->q.'%')
                    ->orwhere('u.phone', 'like', '%'.$request->q.'%')
                    ->orwhere('u.status', '=', $sts);
                $query->orwhere(Db::raw('(DATE_FORMAT(u.last_active, "%m/%d/%Y %h:%m %p"))'), 'like', "%".$request->q."%");
            });
        }

        if (isset($request->fname) && $request->fname != ''){
            $query->where('u.firstname', 'like', '%'.$request->fname.'%');
        }
        if (isset($request->lname) && $request->lname != ''){
            $query->where('u.lastname', 'like', '%'.$request->lname.'%');
        }
        if (isset($request->role) && $request->role != ''){
            $query->where('u.roleid', '=', $request->role);
        }
        if (isset($request->status) && $request->status != ''){
            $query->where('u.status', '=', $request->status);
        }
        if (isset($request->partner) && $request->partner != ''){
            $query->where('u.partner_id', '=', $request->partner);
        }
        if (isset($request->partner_type) && $request->partner_type != ''){
            $query->where('u.partner_type', '=', $request->partner_type);
        }
        return $query->orderBy('id', 'DESC')->get();
    }

    private function sendEmailAccountEnable($user_data){
        $placeholder['FN'] = $user_data->firstname;
        $placeholder['USERNAME'] = $user_data->email;;
        $placeholder['URL'] = $_ENV['SITE_URL'];

        $email_req = new EmailRequest();
        $email_req->setTemplate(EmailTemplates::ENABLE_ACCOUNT_WRONG_PASSWORD)
            ->setPlaceholder($placeholder)
            ->setTo([[$user_data->email, $user_data->firstname]])
            ->setLogSave(true);

        $email_helper = new EmailHelper($email_req);
        $email_helper->sendEmail();
    }
}
