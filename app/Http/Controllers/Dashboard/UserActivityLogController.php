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

class UserActivityLogController extends Controller
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
            ->where('action_via', '!=', 'cron')
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
        $column_setting = ListingSetting::getUserActivityLogsDefaultListing();
        return view('user-activity.index', compact('pages_arr','column_setting'));
    }

    public function ajax(Request $request) {
        if(Auth::user()->roleid != 4 && !UserAccess::hasAccess(UserAccess::USER_ACTIVITY_LOG_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $q = request('q');
        $query = UserActivity::select('pas_user_activity.id', 'action', 'url', 'method', 'ip_address', DB::raw('CONCAT(pas_users.firstname, " ", pas_users.lastname) AS full_name'), DB::raw('url AS breadcrumb'), DB::raw('DATE_FORMAT(created_at, "'.Utility::DEFAULT_DATE_TIME_FORMAT_MYSQL.'") AS created_at'))
            ->leftJoin('pas_users', 'pas_users.id', '=', 'pas_user_activity.created_by');

        $login_query = LoginActivity::select(['pas_login_activity.id', DB::raw('"Login/Logout" AS action'), DB::raw('"login" AS url'), DB::raw('"POST" AS method'), 'ip_address', DB::raw('CONCAT(pas_users.firstname, " ", pas_users.lastname) AS full_name'), DB::raw('"Login/Logout" AS breadcrumb'), DB::raw('CONCAT("IN:", DATE_FORMAT(logged_in_at, "'.Utility::DEFAULT_DATE_TIME_FORMAT_MYSQL.'"), "<br> OUT:", DATE_FORMAT(logged_out_at, "'.Utility::DEFAULT_DATE_TIME_FORMAT_MYSQL.'")) AS created_at')])
            ->join('pas_users', 'pas_users.id', '=', 'pas_login_activity.user_id');

        if (isset($q)){
            $query->where(function ($query) use ($q) {
                $query->where('action', 'like', '%'.$q.'%')
                    ->orwhere('url', 'like', '%'.$q.'%')
                    ->orwhere('method', 'like', '%'.$q.'%')
                    /*->orwhere('old_data', 'like', '%'.$q.'%')
                    ->orwhere('new_data', 'like', '%'.$q.'%')*/
                    ->orwhere('ip_address', 'like', '%'.$q.'%')
                    ->orwhere('user_agent', 'like', '%'.$q.'%')
                    ->orwhere('created_at', 'like', '%'.$q.'%')
                    ->orwhere(DB::raw('CONCAT(pas_users.firstname, " ", pas_users.lastname)'), 'like', '%'.$q.'%');
            });

            $login_query->where(function ($query) use ($q) {
                $query->where('ip_address', 'like', '%'.$q.'%')
                    ->orwhere(DB::raw('CONCAT(pas_users.firstname, " ", pas_users.lastname)'), 'like', '%'.$q.'%');
            });
        }

        if (!empty($request->action)){
            if($request->action == 'Login'){
                $query->where('pas_user_activity.id', '=', '0');
            }else{
                $query->where('action', '=', $request->action);
                $login_query->where('pas_login_activity.id', '=', '0');
            }

        }if (!empty($request->breadcrumb)){
            if($request->breadcrumb == 'Login/Logout'){
                $query->where('pas_user_activity.id', '=', '0');
            }else{
                $query->where('url', '=', $request->breadcrumb);
                $login_query->where('pas_login_activity.id', '=', '0');
            }
            $query->where('url', '=', $request->breadcrumb);
        }

        //$query->whereNotIn('url', ['cron-price-book-program-map']);
        $query->where('action_via', '!=', 'cron');
        $query->where('action', '!=', 'delete');
        $query->orderBy('id', 'DESC');
        $login_query->orderBy('logged_in_at', 'DESC');

        $skip = $request->limit * ($request->page_no - 1);

        $a = DB::table(DB::raw("({$query->toSql()}) as a"))
            ->mergeBindings($query->getQuery())
            ->selectRaw("a.*");

        $b = DB::table(DB::raw("({$login_query->toSql()}) as b"))
            ->mergeBindings($login_query->getQuery())
            ->selectRaw("b.*");

        $a
            ->union($b)
            ->skip($skip)->take($request->limit)
            ->orderBy('id', 'DESC');

        //$total_record = $a->count('id');
        $total_record = 0;


        /*$a->skip($skip);
        $a->take($request->limit);*/

        $logs = $a->get();
        //dd($logs);

        foreach ($logs as $key => $log) {
            $page_detail = UserActivity::getPageDetail($log->url);
            if($page_detail){
                $logs[$key]->breadcrumb = implode(' => ', $page_detail['breadcrumb']);
            }
            $logs[$key]->_id = $log->id;
            $logs[$key]->action = ucwords($log->action);
            $logs[$key]->id = pas_encrypt($log->id);
        }
        $column_setting = ListingSetting::getUserActivityLogsDefaultListing();
        return view('user-activity.view', compact('logs','column_setting', 'total_record'));
        //return response()->json($logs);
    }

    public function view(Request $resuest) {
        if(Auth::user()->roleid != 4 && !UserAccess::hasAccess(UserAccess::USER_ACTIVITY_LOG_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $result = DB::table('pas_user_activity')->where('id', '=', pas_decrypt($resuest->id))->get()->first();
        if($result){
            $act_data = $result->old_data != '' ? json_decode($result->old_data, true): json_decode($result->new_data, true);
            $page_detail = UserActivity::getPageDetail($result->url);
            if($page_detail){
                switch ($result->url){
                    case 'student-store':
                        $data = $act_data;
                        break;
                    case 'myinstitution-update':
                        $data = (new StudentProgressReport())->instituteRequest($act_data);
                        break;
                    case 'update-dashboard':
                        $data = $act_data;
                        break;
                    case 'catalog-change-status':
                        $data = (new Program())->search($act_data);
                        $data->action = ucwords($act_data["action"]);
                        break;
                    case 'student-import-file':
                        $data = $act_data;
                        break;
                    case 'leadssubmit':
                        $data = $act_data;
                        break;
                    case 'marketing-collateral-store':
                        $data = (new StudentProgressReport())->instituteRequest(['ids' => [$act_data['pas_marketing_collateral']['progress_report_id']] ]);
                        //$data = $act_data;
                        break;
                    case 'update-institute-logo':
                        $data = ['old_data' => json_decode($result->old_data, true), 'new_data' => json_decode($result->new_data, true)];
                        //$data = $act_data;
                        break;
                    case 'update-institute-contact':
                        $data = ['old_data' => json_decode($result->old_data, true), 'new_data' => json_decode($result->new_data, true)];
                        break;
                    case 'update-institute-address':
                        $data = ['old_data' => json_decode($result->old_data, true), 'new_data' => json_decode($result->new_data, true)];
                        break;
                    case 'profilesubmit':
                        $data = ['old_data' => json_decode($result->old_data, true), 'new_data' => json_decode($result->new_data, true)];
                        //$data = $act_data;
                        break;
                    case 'partneruserssubmit':
                    case 'partner-users-delete':
                    case 'weuserssubmit':
                    case 'we-users-delete':
                    case 'myusersubmit':
                    case 'my-user-delete':
                    case 'configsubmit':
                    case 'update-news':
                    case 'update-announcements':
                    case 'update-updates':
                    case 'we-template':
                    case 'cron-partner':
                    case 'cron-program':
                    case 'cron-enrollment':
                    case 'cron-deals':
                    case 'cron-schedule':
                    case 'cron-leads':
                    case 'cron-price-book':
                        $data = ['action' => $result->action, 'old_data' => json_decode($result->old_data, true), 'new_data' => json_decode($result->new_data, true), 'price_book' => []];
                        break;
                    case 'cron-price-book-program-map':
                        $data = ['action' => $result->action, 'old_data' => json_decode($result->old_data, true), 'new_data' => json_decode($result->new_data, true), 'price_book' => []];

                        $price_books = DB::table('pas_price_book')->pluck('name','id')->toArray();
                        $price_books_zoho = DB::table('pas_price_book')->pluck('name','zoho_id')->toArray();
                        $programs = DB::table('pas_program')->pluck('name','id')->toArray();

                        if($data['action'] == 'create'){
                            foreach ($data['new_data'] as $price_book_program_maps) {
                                foreach ($price_book_program_maps as $price_book_program_map) {
                                    if(isset($price_books[$price_book_program_map['price_book_id']]) && isset($programs[$price_book_program_map['program_id']])){
                                        $data['price_book'][] = [
                                            'price_book' => $price_books[$price_book_program_map['price_book_id']],
                                            'program' => $programs[$price_book_program_map['program_id']],
                                            'program_list_price' => $price_book_program_map['program_list_price']
                                        ];
                                    }
                                }
                            }
                        }else if($data['action'] == 'delete'){
                            foreach ($data['new_data'] as $price_book_id) {
                                if(isset($price_books_zoho[$price_book_id])){
                                    $data['price_book'][] = $price_books_zoho[$price_book_id];
                                }
                            }
                        }
                        break;
                    case 'login':
                        $data = $act_data;
                        break;
                    default:
                        $data = $act_data;
                }

                return response()->json(['heading' => $page_detail['heading'], 'html' => view('user-activity.'.$page_detail['template'], compact('data'))->render()]);
            }
        }
    }
}
