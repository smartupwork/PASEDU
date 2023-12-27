<?php
namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\Controller;
use App\Models\UserAccess;
use App\UserActivityHelper;
use App\Utility;
use App\Models\ListingSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use Config;
use Lang;

class ConfigemailController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function main()
    {
        if(!UserAccess::hasAccess(UserAccess::CONFIGURATION_EMAIL_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $column_setting = ListingSetting::getConfigurationEmailDefaultListing();
        return view('dashboard.configemail', compact('column_setting'));
    }
    
    public function change_store(Request $request){
        if(!UserAccess::hasAccess(UserAccess::CONFIGURATION_EMAIL_ACCESS, 'add')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $id = pas_decrypt($request->id);
        $idata = array();
        $from_name = request('from_name');
        $idata['from_name'] = $from_name;
        $from_email = request('from_email');
        $idata['from_email'] = $from_email;
        $type = request('type');
        $idata['type'] = $type;
        $subject = request('subject');
        $idata['subject'] = $subject;
        $message = request('message');
        $idata['message'] = $message;
        if($from_name == ''){
            $newarray = array("status"=>"fail", "msg"=>"Please enter from name.");
        }elseif($from_email == ''){
            $newarray = array("status"=>"fail", "msg"=>"Please enter from email.");
        }elseif($subject == ''){
                $newarray = array("status"=>"fail", "msg"=>"Please enter subject.");	
        }elseif($message == ''){
                $newarray = array("status"=>"fail","msg"=>"Please enter message.");		
        }else{	
                $nrs = DB::table('pas_email_templates')->where([["type", '=', $type],['id', '!=', $id]])->count();
                if($nrs == 0){
                    $old_data = DB::table('pas_email_templates')
                        ->select(['from_name', 'from_email', 'type', 'subject', 'message'])
                        ->where("id", '=', $id)
                        ->first();

                    DB::table('pas_email_templates')->where([["id", '=', $id]])->update($idata);

                    $leeds_data['action'] = 'update';
                    $leeds_data['old_data'] = json_encode($old_data);
                    $leeds_data['new_data'] = json_encode($idata);
                    $leeds_data['ref_ids'] = $id;
                    UserActivityHelper::getInstance()->save($request, $leeds_data);

                    $newarray = array("status"=>"success","msg"=>"Record updated successfully.","lid"=>"");
                }/*else{
                    $newarray = array("status"=>"success","msg"=>"This record already exist.");
                }*/
                
        }
        return response()->json($newarray);        
    }
    
    public function feature_ajax() {
        if(!UserAccess::hasAccess(UserAccess::CONFIGURATION_EMAIL_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $q = request('q');
        $query = DB::table('pas_email_templates')->select('id', 'type', 'subject', 'message');

        if (isset($q)){
            $query->Where(function ($query) use ($q) {
            $query->where('type', 'like', '%'.$q.'%')
                        ->orwhere('subject', 'like', '%'.$q.'%')
                        ->orwhere('message', 'like', '%'.$q.'%');
        });
        }

        $records = $query->orderBy('id', 'DESC')->get();

        foreach ($records as $key => $record) {
            $records[$key]->id = pas_encrypt($record->id);
        }
        
        $column_setting = ListingSetting::getConfigurationEmailDefaultListing();
        return view('dashboard._configemail_view', compact('records','column_setting'));
        //return response()->json($records);
    }
    
    
    public function change(Request $request){
        if(!UserAccess::hasAccess(UserAccess::CONFIGURATION_EMAIL_ACCESS, 'add')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $id = pas_decrypt($request->id);
        if(empty($id)){
            return redirect(route('configuration-email'));
        }
        $edata = DB::table('pas_email_templates')->where([["id", '=', pas_decrypt($request->id)]])->first();
        if(!$edata){
            return redirect(route('configuration-email'));
        }

        return view('dashboard.configemailedit', compact('edata'));
    }
}
