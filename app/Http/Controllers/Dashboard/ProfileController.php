<?php
namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\PartnerType;
use App\Models\Roles;
use App\Models\User;
use App\Models\UserAccess;
use App\UserActivityHelper;
use App\Utility;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Session;
use Config;
use Lang;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function main()
    {
        if(!UserAccess::hasAccess(UserAccess::MY_PROFILE_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }

        $memdata = DB::table('pas_users')->where([["id", '=', Auth::user()->id]])->first();
        $roles = Roles::get();
        $partner_types = PartnerType::where('status', '=', Utility::STATUS_ACTIVE)->get();
        $partners = Partner::select(['id', 'partner_name'])->get()->all();
        return view('dashboard.profile', compact('memdata','partners', 'roles', 'partner_types'));
    }

    public function update_profile()
    {
        if(!UserAccess::hasAccess(UserAccess::MY_WE_PROFILE_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }

        $memdata = DB::table('pas_users')->where([["id", '=', Auth::user()->id]])->first();
        $roles = Roles::get();
        $partner_types = PartnerType::where('status', '=', Utility::STATUS_ACTIVE)->get();
        $partners = Partner::select(['id', 'partner_name'])->get()->all();
        return view('dashboard.edit-profile', compact('memdata','partners', 'roles', 'partner_types'));
    }

    // Not Using
    /*public function weUserProfile()
    {
        if(!UserAccess::hasAccess(UserAccess::MY_PROFILE_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }

        $memdata = DB::table('pas_users')->where([["id", '=', Auth::user()->id]])->first();
        $roles = Roles::get();
        $partner_types = PartnerType::where('status', '=', Utility::STATUS_ACTIVE)->get();
        $partners = Partner::select(['id', 'partner_name'])->get()->all();
        return view('dashboard.we-user-profile', compact('memdata','partners', 'roles', 'partner_types'));
    }*/
    
    public function store_profile(Request $request){
        if(!UserAccess::hasAccess(UserAccess::MY_PROFILE_ACCESS, 'add')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $ids = request('ids');
        $idata = array();
        if(Auth::user()->user_type != '3'){
            $phone = request('phone');
            $idata['phone'] = $phone;
        }

        # get file from request object
        $image = request('photo');
        if($image != ''){
            $s3 = \Storage::disk('s3');
            $file_name = uniqid() .'.'. $image->getClientOriginalExtension();
            $s3filePath = '/partner/' . $file_name;
            $s3->put($s3filePath, file_get_contents($image), 'public');
            $idata['photo'] = $file_name;
            $query = DB::table('pas_users')->where([["id", '=', $ids]]);
            $old_data = $query->select('photo', 'phone')->get()->first();
            $query->update($idata);

            $leeds_data['action'] = 'update';
            $leeds_data['old_data'] = json_encode($old_data);
            $leeds_data['new_data'] = json_encode($idata);
            $leeds_data['ref_ids'] = $ids;
            UserActivityHelper::getInstance()->save($request, $leeds_data);

            $newarray = array("status"=>"success","msg"=>"Record updated successfully.");
        }else{
            $newarray = array("status"=>"fail", "msg"=> "Please select profile image.");
        }

        
        return response()->json($newarray);        
    }

    public function store_edit(){
        if(!UserAccess::hasAccess(UserAccess::MY_PROFILE_ACCESS, 'add')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $ids = request('ids');

        if(empty(request('phone'))){
            return response()->json(["status"=>"fail", "msg"=> "Please enter phone number."]);
        }

        $idata = array();
        //if(Auth::user()->user_type != '3'){
        $idata['phone'] = request('phone');
        //}

        $image = request('photo');
        if($image != ''){
            $s3 = \Storage::disk('s3');
            $file_name = uniqid() .'.'. $image->getClientOriginalExtension();
            $s3filePath = '/partner/' . $file_name;
            $s3->put($s3filePath, file_get_contents($image), 'public');
            $idata['photo'] = $file_name;
        }

        DB::table('pas_users')->where([["id", '=', $ids]])->update($idata);
        $newarray = array("status"=>"success","msg"=>"Record updated successfully.");

        return response()->json($newarray);
    }
    
}
