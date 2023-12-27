<?php
namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\State;
use App\Models\UserAccess;
use App\UserActivityHelper;
use App\Utility;
use App\ZohoHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use Config;
use Lang;

class InstituteProfileController extends Controller
{
    const ACCESS_DENIED_VIEW = Utility::ERROR_PAGE_TEMPLATE;
    const ACTIVE_TAB = 'active-tab';
    const PARTNER_DETAIL = 'partner_detail';
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if(!UserAccess::hasAccess(UserAccess::MY_INSTITUTION_PROFILE_ACCESS, 'view')){
            return view(self::ACCESS_DENIED_VIEW);
        }
        if(!Session::has(self::ACTIVE_TAB)){
            Session::flash(self::ACTIVE_TAB, 'logo-tab');
        }
        $partner_data = session(self::PARTNER_DETAIL);
        $partner_data = Partner::find($partner_data['id']);
        $states = State::where('status', '=', 1)->get();
        return view('dashboard.institute-profile', compact('partner_data', 'states'));
    }
    
    public function updateInstituteLogo(Request $request){
        if(!UserAccess::hasAccess(UserAccess::MY_INSTITUTION_PROFILE_ACCESS, 'add')){
            return view(self::ACCESS_DENIED_VIEW);
        }
        $image = $request->logo;
        if($image != ''){
            $s3 = \Storage::disk('s3');
            $file_name = uniqid() .'.'. $image->getClientOriginalExtension();
            $s3filePath = '/partner/' . $file_name;
            $s3->put($s3filePath, file_get_contents($image), 'public');
            $ins_data['logo'] = $file_name;
            $partnerDetail = Session::get(self::PARTNER_DETAIL);
            $query = DB::table('pas_partner')->where([["id", '=', $partnerDetail['id']]]);
            $old_logo = $query->value('logo');
            $query->update($ins_data);

            $zoho_data[0]['id'] = $partnerDetail['zoho_id'];
            $zoho_data[0]['logo'] = $s3filePath;

            $ext = $image->getClientOriginalExtension();

            $newfname = time().".".$ext;
            $image->move(public_path('partners'), $newfname);

            $zoho_response = ZohoHelper::getInstance()->uploadPhoto('Accounts', $partnerDetail['zoho_id'], public_path('partners').'/'.$newfname, $partnerDetail['zoho_id'].'_pic.'.$ext);

            Session::flash(self::ACTIVE_TAB, 'logo-tab');
            if(isset($zoho_response['status']) && $zoho_response['status'] == 'success'){

                $leeds_data['action'] = 'update';
                $leeds_data['old_data'] = json_encode(['logo' => $old_logo]);
                $leeds_data['new_data'] = json_encode(['logo' => $file_name]);
                $leeds_data['ref_ids'] = $partnerDetail['id'];
                UserActivityHelper::getInstance()->save($request, $leeds_data);

                return response()->json(["status"=>"success", "msg"=>"Logo uploaded successfully."]);
            }

        }
        return response()->json(["status"=> "fail", "msg"=>"Logo upload failed."]);
    }

    public function updateInstituteContact(Request $request){
        if(!UserAccess::hasAccess(UserAccess::MY_INSTITUTION_PROFILE_ACCESS, 'add')){
            return view(self::ACCESS_DENIED_VIEW);
        }
        $partnerDetail = Session::get(self::PARTNER_DETAIL);
        $data = $request->all();
        if(empty($data['contact_name']) && empty($data['title']) && empty($data['phone']) && empty($data['email'])){
            return response()->json(["status"=> "fail", "msg"=> 'Please enter at lease one input field.']);
        }
        unset($data['_token']);
        $query = DB::table('pas_partner')->where([["id", '=', $partnerDetail['id']]]);
        $old_data = $query->get()->first();
        $query->update($data);
        Session::flash(self::ACTIVE_TAB, 'contact-tab');

        $leeds_data['action'] = 'update';
        $leeds_data['old_data'] = json_encode($old_data);
        $leeds_data['new_data'] = json_encode($data);
        $leeds_data['ref_ids'] = $partnerDetail['id'];
        UserActivityHelper::getInstance()->save($request, $leeds_data);

        $zoho_data[0]['id'] = $partnerDetail['zoho_id'];
        $zoho_data[0]['TP_Contact_Name'] = $data['contact_name'];
        $zoho_data[0]['TP_Contact_Title'] = $data['title'];
        $zoho_data[0]['TP_Phone'] = $data['phone'];
        $zoho_data[0]['TP_Email'] = $data['email'];
        $response = ZohoHelper::getInstance()->updateRecord($zoho_data, 'Accounts');
        if(isset($response['status']) && $response['status'] == 'error'){
            $detail_error = isset($response['details']['permissions'][0]) ? $response['details']['permissions'][0]: '';
            return response()->json(["status" => "fail", "msg" => 'Contact information updated successfully. ZOHO Error: '.$response['message'].' '.$detail_error]);
        }
        return response()->json(["status"=>"success","msg"=>"Contact information updated successfully."]);
    }

    public function updateInstituteAddress(Request $request){
        if(!UserAccess::hasAccess(UserAccess::MY_INSTITUTION_PROFILE_ACCESS, 'add')){
            return view(self::ACCESS_DENIED_VIEW);
        }
        $partnerDetail = Session::get(self::PARTNER_DETAIL);
        $data = $request->all();
        if(empty($data['street']) && empty($data['city']) && empty($data['state']) && empty($data['zip_code'])){
            return response()->json(["status"=> "fail", "msg"=> 'Please enter at lease one input field.']);
        }
        unset($data['_token']);
        $query = DB::table('pas_partner')->where([["id", '=', $partnerDetail['id']]]);
        $old_data = $query->get()->first();
        $query->update($data);
        Session::flash(self::ACTIVE_TAB, 'address-tab');

        $leeds_data['action'] = 'update';
        $leeds_data['old_data'] = json_encode($old_data);
        $leeds_data['new_data'] = json_encode($data);
        $leeds_data['ref_ids'] = $partnerDetail['id'];
        UserActivityHelper::getInstance()->save($request, $leeds_data);

        $zoho_data[0]['id'] = $partnerDetail['zoho_id'];
        $zoho_data[0]['Shipping_Street'] = $data['street'];
        $zoho_data[0]['Shipping_City'] = $data['city'];
        $zoho_data[0]['Shipping_State'] = $data['state'];
        $zoho_data[0]['Shipping_Code'] = $data['zip_code'];
        ZohoHelper::getInstance()->updateRecord($zoho_data, 'Accounts');

        return response()->json(["status"=>"success","msg"=>"Address updated successfully."]);
    }
    
}
