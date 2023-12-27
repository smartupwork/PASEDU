<?php
namespace App\Http\Controllers\Prestashop;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAccess;
use App\Utility;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Session;
use Config;
use Lang;
require base_path("vendor/autoload.php");
use Cookie;

class BannerNotificationController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getBannerNotificationApi(Request $request)
    {
        $partner = DB::table('pas_partner')->where('partner_name', '=', $request->partner_name)->value('id');

        if(!$partner){
            return response()->json(['status' => false, 'message' => 'Partner does not exists.']);
        }

        $banner_notification = DB::table('ps_configuration')
            ->select(['content'])
            ->where('partner_id', '=', $partner)
            ->where('type', '=', 'banner-notification')
            ->where('is_active', '=', 1)
            ->value('content');

        return response()->json(['status' => true, 'data' => $banner_notification]);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        if(!UserAccess::hasAccess(UserAccess::PARTNER_ADMIN_ACCESS, 'view')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }
        $edit_record = DB::table('ps_configuration')
            ->where('partner_id', '=', User::getPartnerDetail('id'))
            ->where('type', '=', 'banner-notification')
            ->get()->first();

        return view('prestashop.banner-notification.create', compact('edit_record'));
    }


    public function save(Request $request){
        if(!UserAccess::hasAccess(UserAccess::PARTNER_ADMIN_ACCESS, 'add')){
            return view(Utility::ERROR_PAGE_TEMPLATE);
        }

        if($_POST){
            $post = $request->banner_notification;
            $post['partner_id'] = User::getPartnerDetail('id');
            $content = explode(' ', strip_tags($post['content']));
            if(empty($post['content'])){
                return response()->json(["status"=> "fail", "message"=>"Please enter content."]);
            }else if(count($content) > 100){
                return response()->json(["status"=> "fail", "message"=>"Please enter no more than 100 words."]);
            }

            $post['is_active'] = (isset($post['is_active']) && $post['is_active'] == 1) ? 1:0;

            if(!empty($request->id)){
                $post['updated_at'] = date('Y-m-d H:i:s');
                $post['updated_by'] = Auth::user()->id;
                DB::table('ps_configuration')->where('id', '=', $request->id)->update($post);
                $this->updateCache();
                return response()->json(['status' => 'success', 'message' => 'Banner notification successfully updated.']);
            }else{
                $post['created_at'] = date('Y-m-d H:i:s');
                $post['created_by'] = Auth::user()->id;
                DB::table('ps_configuration')->insert($post);
                $this->updateCache();
                return response()->json(['status' => 'success', 'message' => 'Banner notification successfully created.']);
            }
        }
        return response()->json(['status' => 'fail', 'message' => 'Something went wrong.']);
    }

    private function updateCache(){
        $client = new Client();

        $response = $client->get($_ENV['PRESTASHOP_BASE_URL'].'/modules/pasapi/cache-clear.php', [
            'headers' => [
                //'Authorization' => 'Bearer '. $this->access_token,
                'Accept'        => 'application/json',
            ],
            'query_params' => [],
        ]);

        $response->getBody();
    }

}
