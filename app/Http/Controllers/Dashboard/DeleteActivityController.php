<?php
namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\Controller;
use App\Models\LoginActivity;
use App\Models\Program;
use App\Models\StudentProgressReport;
use App\Models\UserAccess;
use App\Models\UserActivity;
use App\Models\ListingSetting;
use App\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Session;
use Config;
use Lang;
require base_path("vendor/autoload.php");

class DeleteActivityController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index() {
        if(Auth::user()->roleid != 4 && !UserAccess::hasAccess(UserAccess::USER_ACTIVITY_LOG_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $pages = DB::table('pas_user_activity')->select(DB::raw('DISTINCT url'))
            ->groupBy('url')
            ->get()->pluck('url');

        $pages_arr = [];
        foreach ($pages as $page) {
            $page_detail = \App\Models\UserActivity::getPageDetail($page);
            if($page_detail){
                $pages_arr[$page] = implode(' => ', $page_detail['breadcrumb']);
            }
        }
        asort($pages_arr);
        $column_setting = ListingSetting::getDeleteActivityLogsDefaultListing();
        return view('deleted-activity.index', compact('pages_arr','column_setting'));
    }

    public function ajax(Request $request) {
        if(Auth::user()->roleid != 4 && !UserAccess::hasAccess(UserAccess::USER_ACTIVITY_LOG_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $query = UserActivity::select('pas_user_activity.id', 'action', 'url', 'method', 'ip_address', DB::raw('CONCAT(pas_users.firstname, " ", pas_users.lastname) AS full_name'), DB::raw('url AS breadcrumb'), DB::raw('DATE_FORMAT(created_at, "'.Utility::DEFAULT_DATE_TIME_FORMAT_MYSQL.'") AS created_at'))
            ->leftJoin('pas_users', 'pas_users.id', '=', 'pas_user_activity.created_by');       

        $query->where('action', 'delete');
        $query->whereNotIn('url', ['cron-price-book-program-map']);
        $query->orderBy('id', 'DESC');

        $total_record = $query->count('pas_user_activity.id');

        $skip = $request->limit * ($request->page_no - 1);

        $logs = $query->skip($skip)->take($request->limit)->get();
        
        foreach ($logs as $key => $log) {
            $page_detail = UserActivity::getPageDetail($log->url);
            if($page_detail){
                $logs[$key]->breadcrumb = implode(' => ', $page_detail['breadcrumb']);
            }
            $logs[$key]->_id = $log->id;
            $logs[$key]->action = ucwords($log->action);
            $logs[$key]->ids = pas_encrypt($log->id);
        }
        //print"<pre>";print_r($logs);die;
        $column_setting = ListingSetting::getDeleteActivityLogsDefaultListing();
        return view('deleted-activity.view', compact('logs','column_setting', 'total_record'));
        //return response()->json($logs);
    }

    public function delete(){
        $ids = request('id');
        $ids_arr = @explode(',', $ids);
        if(count($ids_arr) > 0){
            $ids_arr = array_filter(array_map('pas_decrypt', $ids_arr));
            DB::table('pas_user_activity')->whereIn('id', $ids_arr)->delete();
        }
        $newarray = array("status"=>"success");
        return response()->json($newarray);
    }

    public function revert(Request $request){
        if(Auth::user()->roleid != 4 && !UserAccess::hasAccess(UserAccess::USER_ACTIVITY_LOG_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $result = DB::table('pas_user_activity')->where('id', '=', pas_decrypt($request->id))->get()->first();

        if($result){
            $act_data = $result->old_data != '' ? json_decode($result->old_data, true): json_decode($result->new_data, true);            
            $page_detail = UserActivity::getPageDetail($result->url);
            //print"<pre>";print_r($result);die;
            if($page_detail){
                switch ($result->url){
                    case 'my-user-delete':
                        if(count($act_data) > 0){
                            foreach($act_data as $data){ //print"<pre>";print_r($data);die;
                                $role_id = DB::table('pas_roles')->where("role_name","=",$data['role_name'])->get()->first();
                                $partnerdata = DB::table('pas_partner')->where("partner_name","=",$data['partner_name'])->get()->first();
                                $idata = array(
                                    "id"=>$data['id'],
                                    "user_type"=>$data['user_type'],
                                    "email"=>$data['email'],
                                    "password"=>$data['password'],
                                    "firstname"=>$data['firstname'],
                                    "lastname"=>$data['lastname'],
                                    "last_active"=>$data['last_active'],
                                    "status"=>$data['status'],
                                    "request_time"=>$data['request_time'],
                                    "reset_status"=>$data['reset_status'],
                                    "first_login"=>$data['first_login'],
                                    "login_status"=>$data['login_status'],
                                    "otp"=>$data['otp'],
                                    "photo"=>$data['photo'],
                                    "phone"=>$data['phone'],
                                    "augusoft_campus"=>$data['augusoft_campus'],
                                    "access_level"=>$data['access_level'],
                                    "access_feature"=>$data['access_feature'],
                                    "added_by"=>$data['added_by'],
                                    "login_code"=>$data['login_code'],
                                    "last_wrong_attempted_at"=>$data['last_wrong_attempted_at'],
                                    "highlight_reports"=>$data['highlight_reports'],
                                    "password_expired_at"=>$data['password_expired_at'],
                                    "roleid"=>$role_id->id,
                                    "partner_id"=>$partnerdata->id,
                                );
                                DB::table('pas_users')->insert($idata);
                            }
                        }
                        DB::table('pas_user_activity')->where('id', '=', pas_decrypt($request->id))->delete();
                        return response()->json(["status"=>"success", "msg"=>"Revert successfully"]);
                    case 'partner-users-delete':
                        if(count($act_data) > 0){
                            foreach($act_data as $data){ //print"<pre>";print_r($data);die;
                                $pdata = DB::table('pas_partner_type')->where("partner_type","=",$data['partner_type'])->get()->first();
                                $role_id = DB::table('pas_roles')->where("role_name","=",$data['role_name'])->get()->first();
                                $partnerdata = DB::table('pas_partner')->where("partner_name","=",$data['partner_name'])->get()->first();
                                $idata = array(
                                    "id"=>$data['id'],
                                    "user_type"=>$data['user_type'],
                                    "email"=>$data['email'],
                                    "password"=>$data['password'],
                                    "firstname"=>$data['firstname'],
                                    "lastname"=>$data['lastname'],
                                    "last_active"=>$data['last_active'],
                                    "status"=>$data['status'],
                                    "request_time"=>$data['request_time'],
                                    "reset_status"=>$data['reset_status'],
                                    "first_login"=>$data['first_login'],
                                    "login_status"=>$data['login_status'],
                                    "otp"=>$data['otp'],
                                    "photo"=>$data['photo'],
                                    "phone"=>$data['phone'],
                                    "augusoft_campus"=>$data['augusoft_campus'],
                                    "access_level"=>$data['access_level'],
                                    "access_feature"=>$data['access_feature'],
                                    "added_by"=>$data['added_by'],
                                    "login_code"=>$data['login_code'],
                                    "last_wrong_attempted_at"=>$data['last_wrong_attempted_at'],
                                    "highlight_reports"=>$data['highlight_reports'],
                                    "password_expired_at"=>$data['password_expired_at'],
                                    "partner_type"=>$pdata->id,
                                    "roleid"=>$role_id->id,
                                    "partner_id"=>$partnerdata->id
                                );
                                DB::table('pas_users')->insert($idata);
                            }
                        }
                        DB::table('pas_user_activity')->where('id', '=', pas_decrypt($request->id))->delete();
                        return response()->json(["status"=>"success", "msg"=>"Revert successfully"]);
                    case 'we-users-delete':
                        if(count($act_data) > 0){
                            foreach($act_data as $data){ //print"<pre>";print_r($data);die;
                                $role_id = DB::table('pas_roles')->where("role_name","=",$data['role_name'])->get()->first();
                                $idata = array(
                                    "id"=>$data['id'],
                                    "user_type"=>$data['user_type'],
                                    "email"=>$data['email'],
                                    "password"=>$data['password'],
                                    "firstname"=>$data['firstname'],
                                    "lastname"=>$data['lastname'],
                                    "last_active"=>$data['last_active'],
                                    "status"=>$data['status'],
                                    "request_time"=>$data['request_time'],
                                    "reset_status"=>$data['reset_status'],
                                    "first_login"=>$data['first_login'],
                                    "login_status"=>$data['login_status'],
                                    "otp"=>$data['otp'],
                                    "photo"=>$data['photo'],
                                    "phone"=>$data['phone'],
                                    "added_by"=>$data['added_by'],
                                    "login_code"=>$data['login_code'],
                                    "last_wrong_attempted_at"=>$data['last_wrong_attempted_at'],
                                    "highlight_reports"=>$data['highlight_reports'],
                                    "password_expired_at"=>$data['password_expired_at'],
                                    "roleid"=>$role_id->id
                                );
                                DB::table('pas_users')->insert($idata);
                            }
                        }
                        DB::table('pas_user_activity')->where('id', '=', pas_decrypt($request->id))->delete();
                        return response()->json(["status"=>"success", "msg"=>"Revert successfully"]);
                }
            }

        }
    }
}
