<?php
namespace App\Http\Controllers\Dashboard;
use App\EmailHelper;
use App\EmailRequest;
use App\Http\Controllers\Controller;
use App\Models\UserAccess;
use App\Models\ListingSetting;
use App\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use Config;
use Lang;
require base_path("vendor/autoload.php");

class SystememaillogsController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function main() {
        if(!UserAccess::hasAccess(UserAccess::SYSTEM_EMAIL_LOGS_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $column_setting = ListingSetting::getEmailLogsDefaultListing();
        return view('dashboard.emaillogs', compact('column_setting'));
    }
    
    public function change_store(){
        if(!UserAccess::hasAccess(UserAccess::SYSTEM_EMAIL_LOGS_ACCESS, 'add')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $subject = request('subject');
        $to_email = request('to_email');        
        $message = request('message');
        if($subject == ''){
                $newarray = array("status"=>"fail", "msg"=>"Please enter subject.");	
        }elseif($to_email == ''){
                $newarray = array("status"=>"fail","msg"=>"Please enter to email.");		
        }elseif($message == ''){
                $newarray = array("status"=>"fail","msg"=>"Please enter message.");		
        }else{
                $email_req = new EmailRequest();
                $email_req
                    /*->setFromName($_ENV['FROM_NAME'])
                    ->setFromEmail($_ENV['FROM_EMAIL'])*/
                    ->setSubject($subject)
                    ->setBody($message)
                    ->setTo([[$to_email, $to_email]])
                    //->setTo([['rajneesh@xoomwebdevelopment.com', 'Rajneesh']])
                    ->setLogSave(true);

                $email_helper = new EmailHelper($email_req);
                $email_helper->sendEmail();
                $newarray = array("status"=>"success","msg"=>"Resend Successfully.");
        }
        return response()->json($newarray);        
    }
    
    public function feature_ajax(Request $request) {
        if(!UserAccess::hasAccess(UserAccess::SYSTEM_EMAIL_LOGS_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $q = request('q');
            $query = DB::table('pas_email_logs')->select('id', 'from_email', 'to_email', 'subject', 'message', 'added_date');
            if (isset($q)){
                $query->Where(function ($query) use ($q) {
                $query->where('to_email', 'like', '%'.$q.'%')
                            ->orwhere('subject', 'like', '%'.$q.'%');
            });
            }

        $total_record = $query->count('id');

        $skip = $request->limit * ($request->page_no - 1);

        $records = $query->orderBy('id', 'DESC')
            ->skip($skip)->take($request->limit)->get();

        foreach ($records as $key => $record) {
            $records[$key]->_id = $record->id;
            $records[$key]->id = pas_encrypt($record->id);
        }
        $column_setting = ListingSetting::getEmailLogsDefaultListing();
        return view('dashboard._emaillogs_view', compact('records','column_setting', 'total_record'));
        //return response()->json($records);
    }
    
    
    public function change(Request $request){
        if(!UserAccess::hasAccess(UserAccess::SYSTEM_EMAIL_LOGS_ACCESS, 'add')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $id = pas_decrypt($request->id);
        if(empty($id)){
            return redirect(route('system-email-logs'));
        }
        $edata = DB::table('pas_email_logs')->where([["id", '=', pas_decrypt($request->id)]])->first();
        if(!$edata){
            return redirect(route('system-email-logs'));
        }

        return view('dashboard.emaillogsedit', compact('edata'));
    }
    
    public function remove(){
        if(!UserAccess::hasAccess(UserAccess::SYSTEM_EMAIL_LOGS_ACCESS, 'add')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $ids = request('id');
        $ids_arr = @explode(',', $ids);

        if(count($ids_arr) > 0){
            $ids_arr = array_filter(array_map('pas_decrypt', $ids_arr));
            DB::table('pas_email_logs')->whereIn('id', $ids_arr)->delete();
        }
        $newarray = array("status"=>"success");
        return response()->json($newarray);
    }
    
    
}
